<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

use Tests\TestCase;
use App\Exceptions\IllegalArgumentException;
use App\Exceptions\AuthException;

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
        $this->services['roles']->setMasterRole($this->gas, 'user', $role->id);
        $this->userWithBasePerm = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userWithBasePerm->addRole($role->id, $this->gas);

        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

		$this->supplier = \App\Supplier::factory()->create();
		$this->userWithShippingPerms = $this->createRoleAndUser($this->gas, 'supplier.shippings', $this->supplier);

        \App\User::factory()->count(3)->create(['gas_id' => $this->gas->id]);

        $otherGas = \App\Gas::factory()->create();
        \App\User::factory()->count(3)->create(['gas_id' => $otherGas->id]);
    }

    /*
        Permessi sbagliati su elenco Utenti
    */
    public function testFailsToListUsers()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['users']->list();
    }

    /*
        Elenco Utenti corretto
    */
    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $users = $this->services['users']->list();

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
        $users = $this->services['users']->list();
        $initial_count = $users->count();

        $user = $users->random();
        $this->services['users']->update($user->id, [
            'status' => 'deleted',
            'deleted_at' => printableDate(date('Y-m-d')),
            'suspended_at' => printableDate(date('Y-m-d')),
        ]);

        $this->actingAs($this->userWithViewPerm);
        $users = $this->services['users']->list();
        $this->assertEquals($initial_count - 1, $users->count());
    }

    /*
        Elenco Utenti con parametri di ricerca
    */
    public function testListWithSearchParam()
    {
        $this->actingAs($this->userWithViewPerm);

        $user1 = \App\User::factory()->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'pippo'
        ]);

        $user2 = \App\User::factory()->create([
            'gas_id' => $this->gas->id,
            'lastname' => 'super pippo'
        ]);

        \App\User::factory()->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'luigi'
        ]);

        $users = $this->services['users']->list('pippo');
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

        $users = $this->services['users']->list();

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
        $this->services['users']->store([]);
    }

    /*
        Salvataggio Utente con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = $this->services['users']->store([
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ]);

        $this->assertEquals('test user', $newUser->username);
        $this->assertTrue(\Hash::check('password', $newUser->password));
        $this->assertEquals('rossi mario', $newUser->printableName());
        $this->assertEquals(0, $newUser->pending_balance);
    }

    /*
        Salvataggio Utente con token per il primo accesso valido
    */
    public function testStoreAndMail()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = $this->services['users']->store([
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'sendmail' => true,
            'email' => 'mario@example.com'
        ]);

        $newUser = $this->services['users']->show($newUser->id);
        $this->assertNotEquals('', $newUser->access_token);
        $this->assertNotNull($newUser->access_token);
        $this->assertEquals(10, strlen($newUser->access_token));
    }

    /*
        Salvataggio Amico
    */
    public function testStoreFriend()
    {
        $this->actingAs($this->userWithViewPerm);

        $newUser = $this->services['users']->storeFriend([
            'username' => 'test friend user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ]);

        $this->nextRound();

        $parent = $this->services['users']->show($this->userWithViewPerm->id);

        $this->assertEquals('test friend user', $newUser->username);
        $this->assertEquals(1, $parent->friends->count());
        $this->assertEquals($parent->id, $newUser->parent_id);
        $this->assertTrue(\Hash::check('password', $newUser->password));
        $this->assertEquals('rossi mario', $newUser->printableName());
        $this->assertEquals(0, $newUser->pending_balance);
    }

    /*
        Promozione amico a utente regolare
    */
    public function testPromoteFriend()
    {
        $this->assertEquals(0, $this->userWithViewPerm->friends()->count());
        $friend = $this->createFriend($this->userWithViewPerm);

        $this->nextRound();

        $parent = $this->services['users']->show($this->userWithViewPerm->id);
        $this->assertEquals(1, $parent->friends()->count());

        $this->nextRound();

        $admin = $this->services['users']->show($this->userWithAdminPerm->id);
        $this->actingAs($admin);
        $this->services['users']->promoteFriend(['email' => 'foobar@example.com'], $friend->id);

        $this->nextRound();

        $friend = $this->services['users']->show($friend->id);
        $parent = $this->services['users']->show($this->userWithViewPerm->id);
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

        $admin = $this->services['users']->show($this->userWithAdminPerm->id);
        $this->actingAs($admin);
        $this->services['users']->reassignFriend($friend->id, $this->userWithBasePerm->id);

        $this->nextRound();

        $friend = $this->services['users']->show($friend->id);
        $old_parent = $this->services['users']->show($this->userWithViewPerm->id);
        $new_parent = $this->services['users']->show($this->userWithBasePerm->id);
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
        $this->services['users']->update($this->userWithViewPerm->id, []);
    }

    /*
        Modifica Utente con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['users']->update('id', []);
    }

    /*
        Modifica Utente con permessi corretti
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = \App\User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        $updatedUser = $this->services['users']->update($user->id, [
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
        $sample = \App\User::inRandomOrder()->first();

        $user = \App\User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        $this->services['users']->update($user->id, [
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
        $sample = \App\User::where('gas_id', $this->gas->id)->where('card_number', '!=', '')->whereNotNull('card_number')->inRandomOrder()->first();

        $user = \App\User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        $this->services['users']->update($user->id, [
            'card_number' => $sample->card_number,
        ]);
    }

    /*
        Modifica del proprio Utente con permessi sbagliati
    */
    public function testFailsToSelfUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $user = $this->services['users']->update($this->userWithNoPerms->id, [
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

        $user = $this->services['users']->update($this->userWithBasePerm->id, [
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

        $user = $this->services['users']->update($this->userWithBasePerm->id, [
            'contact_id' => ['', '', '', ''],
            'contact_type' => ['phone', 'website', 'email', 'address'],
            'contact_value' => ['1234567890', 'http://www.example.com', 'test@mailinator.com', 'Via Pippo, Torino'],
        ]);

        $this->assertEquals($this->userWithBasePerm->id, $user->id);

        $this->nextRound();

        $user = $this->services['users']->show($this->userWithBasePerm->id);
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

        $user = $this->services['users']->update($this->userWithNoPerms->id, [
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
        $this->services['users']->show($this->userWithViewPerm->id);
    }

    /*
        Accesso Utente con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->services['users']->show('random');
    }

    /*
        Accesso Utente
    */
    public function testShow()
    {
        $this->actingAs($this->userWithViewPerm);

        $user = $this->services['users']->show($this->userWithViewPerm->id);

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
        $movement->sender_type = 'App\User';
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
        $this->services['users']->destroy($this->userWithNoPerms->id);
    }

    /*
        Cancellazione Utente
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = $this->services['users']->destroy($this->userWithNoPerms->id);
        $user = $this->services['users']->show($this->userWithNoPerms->id);
        $this->assertEquals($this->userWithNoPerms->id, $user->id);
        $this->assertNotNull($user->deleted_at);

        $user = $this->services['users']->destroy($this->userWithNoPerms->id);

        try {
            $this->services['users']->show($this->userWithNoPerms->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }
    }
}
