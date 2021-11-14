<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class UsersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->gas = \App\Gas::factory()->create();

        $this->userWithViewPerm = $this->createRoleAndUser($this->gas, 'users.view,users.subusers');
        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'users.admin');
        $this->userWithMovementPerm = $this->createRoleAndUser($this->gas, 'movements.admin');
        $this->userWithBasePerm = $this->createRoleAndUser($this->gas, 'users.self');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        \App\User::factory()->count(3)->create(['gas_id' => $this->gas->id]);

        $otherGas = \App\Gas::factory()->create();
        \App\User::factory()->count(3)->create(['gas_id' => $otherGas->id]);

        $this->usersService = new \App\Services\UsersService();
    }

    /*
        Permessi sbagliati su elenco Utenti
    */
    public function testFailsToListUsers()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->usersService->list();
    }

    /*
        Elenco Utenti corretto
    */
    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $users = $this->usersService->list();
        $this->assertCount(8, $users);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }
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

        $users = $this->usersService->list('pippo');
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
        $this->usersService->store(array());
    }

    /*
        Salvataggio Utente con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newUser = $this->usersService->store(array(
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
        Salvataggio Amico
    */
    public function testStoreFriend()
    {
        $this->actingAs($this->userWithViewPerm);

        $newUser = $this->usersService->storeFriend(array(
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
        $this->usersService->update($this->userWithViewPerm->id, array());
    }

    /*
        Modifica Utente con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->usersService->update('id', array());
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

        $updatedUser = $this->usersService->update($user->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

        $this->assertNotEquals($user->birthday, $updatedUser->birthday);
        $this->assertEquals(0, $updatedUser->pending_balance);
    }

    /*
        Modifica del proprio Utente con permessi sbagliati
    */
    public function testFailsToSelfUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $user = $this->usersService->update($this->userWithNoPerms->id, array(
            'password' => 'new password',
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

        $user = $this->usersService->update($this->userWithBasePerm->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

        $this->assertEquals($this->userWithBasePerm->id, $user->id);
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

        $user = $this->usersService->update($this->userWithNoPerms->id, array(
            'password' => 'new password',
        ));

        $this->assertEquals($this->userWithNoPerms->id, $user->id);
    }

    /*
        Accesso Utente con permessi sbagliati
    */
    public function testFailsToShow()
    {
        $this->expectException(AuthException::class);
        $this->usersService->show($this->userWithViewPerm->id);
    }

    /*
        Accesso Utente con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->usersService->show('random');
    }

    /*
        Accesso Utente
    */
    public function testShow()
    {
        $this->actingAs($this->userWithViewPerm);

        $user = $this->usersService->show($this->userWithViewPerm->id);

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
        $this->usersService->destroy($this->userWithNoPerms->id);
    }

    /*
        Cancellazione Utente
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = $this->usersService->destroy($this->userWithNoPerms->id);
        $user = $this->usersService->show($this->userWithNoPerms->id);
        $this->assertEquals($this->userWithNoPerms->id, $user->id);
        $this->assertNotNull($user->deleted_at);

        $user = $this->usersService->destroy($this->userWithNoPerms->id);

        try {
            $this->usersService->show($this->userWithNoPerms->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }
    }
}
