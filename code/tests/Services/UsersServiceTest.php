<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $this->userWithBasePerm = $this->createRoleAndUser($this->gas, 'users.self');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

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
            In inizializzazione sono creati 3 utenti generici, più 5 con
            permessi particolari, e c'è userAdmin sempre inizializzato per tutti
            in TestCase
        */
        $this->assertCount(9, $users);
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
        Salvataggio Utente con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->services['users']->store(array());
    }

    /*
        Salvataggio Utente con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = $this->services['users']->store(array(
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ));

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

        $newUser = $this->services['users']->store(array(
            'username' => 'test user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'sendmail' => true,
            'email' => 'mario@example.com'
        ));

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

        $newUser = $this->services['users']->storeFriend(array(
            'username' => 'test friend user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ));

        $this->assertEquals('test friend user', $newUser->username);
        $this->assertEquals($this->userWithViewPerm->id, $newUser->parent_id);
        $this->assertTrue(\Hash::check('password', $newUser->password));
        $this->assertEquals('rossi mario', $newUser->printableName());
        $this->assertEquals(0, $newUser->pending_balance);
    }

    /*
        Modifica Utente con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->services['users']->update($this->userWithViewPerm->id, array());
    }

    /*
        Modifica Utente con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['users']->update('id', array());
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

        $updatedUser = $this->services['users']->update($user->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

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

        $this->services['users']->update($user->id, array(
            'username' => $sample->username,
        ));
    }

    /*
        Modifica Utente con parametri errati
    */
    public function testInvalidCardNumber()
    {
        $this->expectException(IllegalArgumentException::class);

        $this->actingAs($this->userWithAdminPerm);
        $sample = \App\User::where('card_number', '!=', '')->whereNotNull('card_number')->inRandomOrder()->first();

        $user = \App\User::factory()->create([
            'gas_id' => $this->gas->id
        ]);

        $this->services['users']->update($user->id, array(
            'card_number' => $sample->card_number,
        ));
    }

    /*
        Modifica del proprio Utente con permessi sbagliati
    */
    public function testFailsToSelfUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $user = $this->services['users']->update($this->userWithNoPerms->id, array(
            'name' => 'Mario',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

        $this->assertEquals($this->userWithNoPerms->id, $user->id);
    }

    /*
        Modifica del proprio Utente con permessi corretti
    */
    public function testSelfUpdate()
    {
        $this->actingAs($this->userWithBasePerm);

        $user = $this->services['users']->update($this->userWithBasePerm->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
            'contact_id' => ['', '', ''],
            'contact_type' => ['phone', 'website', 'email'],
            'contact_value' => ['1234567890', 'http://www.example.com', 'test@mailinator.com'],
        ));

        $this->assertEquals($this->userWithBasePerm->id, $user->id);
        $this->assertEquals(3, $user->contacts()->count());
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

        $user = $this->services['users']->update($this->userWithNoPerms->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

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
