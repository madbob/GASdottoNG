<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class UsersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = factory(\App\Gas::class)->create();

        $this->userWithViewPerm = $this->createRoleAndUser($this->gas, 'users.view,users.subusers');
        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'users.admin');
        $this->userWithMovementPerm = $this->createRoleAndUser($this->gas, 'movements.admin');
        $this->userWithBasePerm = $this->createRoleAndUser($this->gas, 'users.self');
        $this->userWithNoPerms = factory(\App\User::class)->create(['gas_id' => $this->gas->id]);

        factory(\App\User::class, 3)->create(['gas_id' => $this->gas->id]);

        $otherGas = factory(\App\Gas::class)->create();
        factory(\App\User::class, 3)->create(['gas_id' => $otherGas->id]);

        Model::reguard();

        $this->usersService = new \App\Services\UsersService();
    }

    public function testFailsToListUsers()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->usersService->list();
    }

    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $users = $this->usersService->list();
        $this->assertCount(8, $users);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }
    }

    public function testListWithSearchParam()
    {
        $this->actingAs($this->userWithViewPerm);

        $user1 = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'pippo'
        ]);

        $user2 = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id,
            'lastname' => 'super pippo'
        ]);

        factory(\App\User::class)->create([
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

    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->usersService->store(array());
    }

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

    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->usersService->update($this->userWithViewPerm->id, array());
    }

    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->usersService->update('id', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        $updatedUser = $this->usersService->update($user->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

        $this->assertNotEquals($user->birthday, $updatedUser->birthday);
        $this->assertEquals(0, $updatedUser->pending_balance);
    }

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

    public function testSelfUpdate()
    {
        $this->actingAs($this->userWithBasePerm);

        $user = $this->usersService->update($this->userWithBasePerm->id, array(
            'password' => 'new password',
            'birthday' => 'Giovedi 01 Dicembre 2016',
        ));

        $this->assertEquals($this->userWithBasePerm->id, $user->id);
    }

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

    public function testFailsToShow()
    {
        $this->expectException(AuthException::class);
        $this->usersService->show($this->userWithViewPerm->id);
    }

    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->usersService->show('random');
    }

    public function testShow()
    {
        $this->actingAs($this->userWithViewPerm);

        $user = $this->usersService->show($this->userWithViewPerm->id);

        $this->assertEquals($this->userWithViewPerm->id, $user->id);
        $this->assertEquals($this->userWithViewPerm->firstname, $user->firstname);
        $this->assertEquals($this->userWithViewPerm->lastname, $user->lastname);
    }

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

    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithViewPerm);
        $this->usersService->destroy($this->userWithNoPerms->id);
    }

    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = $this->usersService->destroy($this->userWithNoPerms->id);
        $user = $this->usersService->show($this->userWithNoPerms->id);
        $this->assertEquals($this->userWithNoPerms->id, $user->id);
        $this->assertNotEquals(null, $user->deleted_at);

        $user = $this->usersService->destroy($this->userWithNoPerms->id);

        try {
            $this->usersService->show($this->userWithNoPerms->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            //good boy
        }
    }
}
