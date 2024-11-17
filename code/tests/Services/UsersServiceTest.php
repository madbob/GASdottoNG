<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Tests\TestCase;
use App\Exceptions\IllegalArgumentException;
use App\Exceptions\AuthException;
use App\User;
use App\Supplier;

class UsersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userWithViewPerm = $this->createRoleAndUser($this->gas, 'users.view,users.subusers');
        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'users.admin');
        $this->userWithMovementPerm = $this->createRoleAndUser($this->gas, 'movements.admin');

        $this->actingAs($this->userAdmin);
        $role = \App\Role::factory()->create(['actions' => 'users.self,users.subusers']);
        app()->make('RolesService')->setMasterRole($this->gas, 'user', $role->id);
        $this->userWithBasePerm = User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userWithBasePerm->addRole($role->id, $this->gas);

        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);

		$this->supplier = Supplier::factory()->create();
		$this->userWithShippingPerms = $this->createRoleAndUser($this->gas, 'supplier.shippings', $this->supplier);

        User::factory()->count(3)->create(['gas_id' => $this->gas->id]);

        $otherGas = \App\Gas::factory()->create();
        User::factory()->count(3)->create(['gas_id' => $otherGas->id]);
    }

    /*
        Permessi sbagliati su elenco Utenti
    */
    public function testFailsToListUsers()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('UsersService')->list();
    }

    /*
        Elenco Utenti corretto
    */
    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $users = app()->make('UsersService')->list();

        /*
            In inizializzazione sono creati 3 utenti generici, più 6 con
            permessi particolari, e c'è userAdmin sempre inizializzato per tutti
            in TestCase
        */
        $this->assertCount(10, $users);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }
    }

    /*
        Cessazione utente
    */
    public function testCease()
    {
        $this->actingAs($this->userWithAdminPerm);
        $users = app()->make('UsersService')->list();
        $initial_count = $users->count();
		$initial_count_db = User::fullEnabled()->count();

        $user = $users->random();
        app()->make('UsersService')->update($user->id, [
            'status' => 'deleted',
            'deleted_at' => printableDate(date('Y-m-d')),
            'suspended_at' => printableDate(date('Y-m-d')),
        ]);

		$this->nextRound();

        $this->actingAs($this->userWithViewPerm);
        $users = app()->make('UsersService')->list('', true);
        $this->assertEquals($initial_count - 1, $users->count());
		$this->assertEquals($initial_count_db - 1, User::fullEnabled()->count());
    }

	/*
		Sospensione utente
    */
	public function testSuspend()
    {
        $this->actingAs($this->userWithAdminPerm);
        $users = app()->make('UsersService')->list();
        $initial_count = $users->count();
		$initial_count_db = User::fullEnabled()->count();

        $user = $users->random();
        app()->make('UsersService')->update($user->id, [
			'status' => 'suspended',
			'deleted_at' => printableDate(date('Y-m-d')),
            'suspended_at' => printableDate(date('Y-m-d')),
        ]);

		$this->nextRound();

        $this->actingAs($this->userWithViewPerm);
        $users = app()->make('UsersService')->list('', true);
        $this->assertEquals($initial_count, $users->count());
		$this->assertEquals($initial_count_db - 1, User::fullEnabled()->count());
    }

    /*
        Elenco Utenti con parametri di ricerca
    */
    public function testListWithSearchParam()
    {
        $this->actingAs($this->userWithViewPerm);

        $user1 = User::factory()->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'pippo'
        ]);

        $user2 = User::factory()->create([
            'gas_id' => $this->gas->id,
            'lastname' => 'super pippo'
        ]);

        User::factory()->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'luigi'
        ]);

        $users = app()->make('UsersService')->list('pippo');
        $this->assertCount(2, $users);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }

        $findByID = function ($id) {
            return function ($user) use ($id) {
                return strcmp($user['id'], $id) == 0;
            };
        };

        $this->assertCount(1, array_filter($users->toArray(), $findByID($user1->id)));
        $this->assertCount(1, array_filter($users->toArray(), $findByID($user2->id)));
    }

	/*
        Elenco Utenti con permessi per le consegne
    */
    public function testListShipping()
    {
        $this->actingAs($this->userWithShippingPerms);

        $users = app()->make('UsersService')->list();

        $this->assertTrue($users->count() != 0);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }
    }

    /*
        Salvataggio Utente con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        app()->make('UsersService')->store([]);
    }

    /*
        Salvataggio Utente con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = app()->make('UsersService')->store([
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ]);

        $this->assertEquals('test user', $newUser->username);
        $this->assertTrue(Hash::check('password', $newUser->password));
        $this->assertEquals('rossi mario', $newUser->printableName());
        $this->assertEquals(0, $newUser->pending_balance);
    }

    /*
        Salvataggio Utente con token per il primo accesso valido
    */
    public function testStoreAndMail()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = app()->make('UsersService')->store([
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'sendmail' => true,
            'email' => 'mario@example.com'
        ]);

        $newUser = app()->make('UsersService')->show($newUser->id);
        $this->assertNotEquals('', $newUser->access_token);
        $this->assertNotNull($newUser->access_token);
        $this->assertEquals(10, strlen($newUser->access_token));
    }

    /*
        Salvataggio Amico
    */
    public function testStoreFriend()
    {
        $initial_count = User::query()->creditable()->count();

        $this->actingAs($this->userWithViewPerm);

        $newUser = app()->make('UsersService')->storeFriend([
            'username' => 'test friend user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ]);

        $this->nextRound();

        $parent = app()->make('UsersService')->show($this->userWithViewPerm->id);
        $newUser = app()->make('UsersService')->show($newUser->id);

        $this->assertEquals('test friend user', $newUser->username);
        $this->assertEquals(1, $parent->friends->count());
        $this->assertEquals($parent->id, $newUser->parent_id);
        $this->assertTrue(Hash::check('password', $newUser->password));
        $this->assertEquals('rossi mario', $newUser->printableName());
        $this->assertEquals(0, $newUser->pending_balance);

        $this->assertEquals($initial_count, User::query()->creditable()->count());
    }

    /*
        Promozione amico a utente regolare
    */
    public function testPromoteFriend()
    {
        $this->assertEquals(0, $this->userWithViewPerm->friends()->count());
        $friend = $this->createFriend($this->userWithViewPerm);

        $this->nextRound();

        $parent = app()->make('UsersService')->show($this->userWithViewPerm->id);
        $this->assertEquals(1, $parent->friends()->count());

        $this->nextRound();

        $admin = app()->make('UsersService')->show($this->userWithAdminPerm->id);
        $this->actingAs($admin);
        app()->make('UsersService')->promoteFriend(['email' => 'foobar@example.com'], $friend->id);

        $this->nextRound();

        $friend = app()->make('UsersService')->show($friend->id);
        $parent = app()->make('UsersService')->show($this->userWithViewPerm->id);
        $this->assertEquals(0, $parent->friends()->count());
        $this->assertEquals('foobar@example.com', $friend->email);
        $this->assertNull($friend->parent_id);
    }

    /*
        Riassegnazione amico
    */
    public function testAssignFriend()
    {
        $this->assertEquals(0, $this->userWithViewPerm->friends()->count());
        $this->assertEquals(0, $this->userWithBasePerm->friends()->count());
        $friend = $this->createFriend($this->userWithViewPerm);

        $this->nextRound();

        $admin = app()->make('UsersService')->show($this->userWithAdminPerm->id);
        $this->actingAs($admin);
        app()->make('UsersService')->reassignFriend($friend->id, $this->userWithBasePerm->id);

        $this->nextRound();

        $friend = app()->make('UsersService')->show($friend->id);
        $old_parent = app()->make('UsersService')->show($this->userWithViewPerm->id);
        $new_parent = app()->make('UsersService')->show($this->userWithBasePerm->id);
        $this->assertEquals(0, $old_parent->friends()->count());
        $this->assertEquals(1, $new_parent->friends()->count());
        $this->assertEquals($this->userWithBasePerm->id, $friend->parent_id);
    }

    /*
        Modifica Utente con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        app()->make('UsersService')->update($this->userWithViewPerm->id, []);
    }

    /*
        Modifica Utente con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('UsersService')->update('id', []);
    }

    /*
        Modifica Utente con permessi corretti
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        $updatedUser = app()->make('UsersService')->update($user->id, [
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ]);

        $this->assertNotEquals($user->birthday, $updatedUser->birthday);
        $this->assertEquals(0, $updatedUser->pending_balance);
    }

    /*
        Modifica Utente con parametri errati
    */
    public function testInvalidUsername()
    {
        $this->expectException(IllegalArgumentException::class);

        $this->actingAs($this->userWithAdminPerm);
        $sample = User::inRandomOrder()->first();

        $user = User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        app()->make('UsersService')->update($user->id, [
            'username' => $sample->username,
        ]);
    }

    /*
        Modifica Utente con parametri errati
    */
    public function testInvalidCardNumber()
    {
        $this->expectException(IllegalArgumentException::class);

        $this->actingAs($this->userWithAdminPerm);
        $sample = User::where('gas_id', $this->gas->id)->where('card_number', '!=', '')->whereNotNull('card_number')->inRandomOrder()->first();

        $user = User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        app()->make('UsersService')->update($user->id, [
            'card_number' => $sample->card_number,
        ]);
    }

    /*
        Modifica Utente con parametri errati
    */
    public function testInvalidName()
    {
        $this->expectException(IllegalArgumentException::class);

        $this->actingAs($this->userWithAdminPerm);
        $sample = User::where('gas_id', $this->gas->id)->inRandomOrder()->first();

        app()->make('UsersService')->store([
            'username' => Str::random(10),
            'firstname' => $sample->firstname,
            'lastname' => $sample->lastname,
            'password' => Str::random(10),
        ]);
    }

    /*
        Modifica del proprio Utente con permessi sbagliati
    */
    public function testFailsToSelfUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $user = app()->make('UsersService')->update($this->userWithNoPerms->id, [
            'name' => 'Mario',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ]);

        $this->assertEquals($this->userWithNoPerms->id, $user->id);
    }

    /*
        Modifica del proprio Utente con permessi corretti
    */
    public function testSelfUpdate()
    {
        $this->actingAs($this->userWithBasePerm);

        $user = app()->make('UsersService')->update($this->userWithBasePerm->id, [
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ]);

        $this->assertEquals($this->userWithBasePerm->id, $user->id);

        $this->nextRound();

        $this->assertTrue(Auth::attempt(['username' => $this->userWithBasePerm->username, 'password' => 'new password']));
    }

    /*
        Salvataggio contatti
    */
    public function testContacts()
    {
        $this->actingAs($this->userWithBasePerm);

        $user = app()->make('UsersService')->update($this->userWithBasePerm->id, [
            'contact_id' => ['', '', '', ''],
            'contact_type' => ['phone', 'website', 'email', 'address'],
            'contact_value' => ['1234567890', 'http://www.example.com', 'test@mailinator.com', 'Via Pippo, Torino'],
        ]);

        $this->assertEquals($this->userWithBasePerm->id, $user->id);

        $this->nextRound();

        $user = app()->make('UsersService')->show($this->userWithBasePerm->id);
        $this->assertEquals(4, $user->contacts->count());

        $tested_types = [];

        foreach($user->contacts as $contact) {
            $this->assertEquals($user->id, $contact->target_id);
            $this->assertEquals(get_class($user), $contact->target_type);
            $this->assertNotEquals('???', $contact->type_name);
            $tested_types[] = $contact->type;

            if ($contact->type == 'address') {
                $add = $contact->asAddress();
                $this->assertEquals(3, count($add));
                $this->assertEquals('Via Pippo', $add[0]);
                $this->assertEquals('Torino', $add[1]);
                $this->assertEquals('', $add[2]);
            }
        }

        $this->assertTrue(in_array('phone', $tested_types));
        $this->assertTrue(in_array('website', $tested_types));
        $this->assertTrue(in_array('email', $tested_types));
        $this->assertTrue(in_array('address', $tested_types));
    }

    /*
        Username come email
    */
    public function testUsernameAsEmail()
    {
        $this->actingAs($this->userWithBasePerm);

        app()->make('UsersService')->update($this->userWithBasePerm->id, [
            'username' => 'test2@mailinator.com',
        ]);

        $this->nextRound();

        $user = app()->make('UsersService')->show($this->userWithBasePerm->id);
        $this->assertEquals(0, $user->contacts->count());
        $this->assertEquals('test2@mailinator.com', $user->username);
        $this->assertEquals('test2@mailinator.com', $user->email);
    }

    /*
        Modifica del proprio Utente con permessi limitati
    */
    public function testLimitedSelfUpdate()
    {
        /*
            Un utente senza permessi deve comunque poter modificare la propria
            password
        */
        $this->actingAs($this->userWithNoPerms);
        $old_birthday = $this->userWithNoPerms->birthday;

        $user = app()->make('UsersService')->update($this->userWithNoPerms->id, [
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ]);

        $this->assertEquals($this->userWithNoPerms->id, $user->id);
        $this->assertEquals($old_birthday, $user->birthday);
    }

    /*
        Accesso Utente con permessi sbagliati
    */
    public function testFailsToShow()
    {
        $this->expectException(AuthException::class);
        app()->make('UsersService')->show($this->userWithViewPerm->id);
    }

    /*
        Accesso Utente con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithViewPerm);
        app()->make('UsersService')->show('random');
    }

    /*
        Accesso Utente
    */
    public function testShow()
    {
        $this->actingAs($this->userWithViewPerm);

        $user = app()->make('UsersService')->show($this->userWithViewPerm->id);

        $this->assertEquals($this->userWithViewPerm->id, $user->id);
        $this->assertEquals($this->userWithViewPerm->firstname, $user->firstname);
        $this->assertEquals($this->userWithViewPerm->lastname, $user->lastname);
    }

    /*
        Pagamento e assegnazione quota di iscrizione
    */
    public function testAnnualFee()
    {
        $this->actingAs($this->userWithMovementPerm);

        $this->assertEquals(null, $this->userWithNoPerms->fee);

        $movement = new \App\Movement();
        $movement->type = 'annual-fee';
        $movement->sender_type = User::class;
        $movement->sender_id = $this->userWithNoPerms->id;
        $movement->target_type = 'App\Gas';
        $movement->target_id = $this->gas->id;
        $movement->date = date('Y-m-d');
        $movement->amount = 10;
        $movement->method = 'cash';
        $movement->save();

        $this->userWithNoPerms = $this->userWithNoPerms->fresh();
        $this->assertEquals($movement->id, $this->userWithNoPerms->fee_id);
    }

    /*
        Cancellazione Utente con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        app()->make('UsersService')->destroy($this->userWithNoPerms->id);
    }

    /*
        Cancellazione Utente
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = app()->make('UsersService')->destroy($this->userWithNoPerms->id);
        $user = app()->make('UsersService')->show($this->userWithNoPerms->id);
        $this->assertEquals($this->userWithNoPerms->id, $user->id);
        $this->assertNotNull($user->deleted_at);

        $user = app()->make('UsersService')->destroy($this->userWithNoPerms->id);

        try {
            app()->make('UsersService')->show($this->userWithNoPerms->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }
    }
}
