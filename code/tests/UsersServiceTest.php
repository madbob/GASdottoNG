<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersServiceTest extends TestCase
{

    use DatabaseTransactions;

    private $userWithViewPerm;
    private $userWithAdminPerm;
    private $userWithNoPerms;
    private $gas;
    private $usersService;

    public function setUp()
    {
        parent::setUp();

        $this->gas = factory(App\Gas::class)->create();

        $this->userWithViewPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        factory(App\Permission::class)->create([
            'user_id' => $this->userWithViewPerm->id,
            'target_id' => $this->gas->id,
            'action' => 'users.view'
        ]);

        $this->userWithAdminPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        factory(App\Permission::class)->create([
            'user_id' => $this->userWithAdminPerm->id,
            'target_id' => $this->gas->id,
            'action' => 'users.admin'
        ]);

        $this->userWithNoPerms = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        factory(App\User::class, 3)->create([
            'gas_id' => $this->gas->id
        ]);

        $otherGas = factory(App\Gas::class)->create();
        factory(App\User::class, 3)->create([
            'gas_id' => $otherGas->id
        ]);

        $this->usersService = new \App\UsersService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToListUsers()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->usersService->listUsers();
    }

    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $users = $this->usersService->listUsers();
        $this->assertCount(6, $users);
        foreach ($users as $user) {
            $this->assertEquals($this->gas->id, $user->gas_id);
        }
    }

    public function testListWithSearchParam()
    {
        $this->actingAs($this->userWithViewPerm);

        $user1 = factory(App\User::class)->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'supermario'
        ]);

        $user2 = factory(App\User::class)->create([
            'gas_id' => $this->gas->id,
            'lastname' => 'mariobros'
        ]);

        factory(App\User::class)->create([
            'gas_id' => $this->gas->id,
            'firstname' => 'luigi'
        ]);

        $users = $this->usersService->listUsers('mario');
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

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToStore()
    {
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
            'email' => 'mr@example.com',
            'password' => 'password'
        ));

        $this->assertEquals('test user', $newUser->username);
        $this->assertEquals(0, $newUser->balance);
        $this->assertTrue(Hash::check('password', $newUser->password));
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToUpdate()
    {
        $this->actingAs($this->userWithViewPerm);

        $this->usersService->update('id', array());
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->usersService->update('id', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        $updatedUser = $this->usersService->update($user->id, array(
            'password' => 'new password',
            'email' => 'mr@example.com',
            'birthday' => 'Thursday 01 December 2016',
        ));

        $this->assertNotEquals($user->email, $updatedUser->email);
        $this->assertNotEquals($user->birthday, $updatedUser->birthday);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToShow()
    {
        $this->usersService->show($this->userWithViewPerm->id);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToShowInexistent()
    {
        $this->actingAs($this->userWithViewPerm);

        $this->usersService->show('random');
    }

    public function testShow()
    {
        $this->actingAs($this->userWithViewPerm);

        $user = $this->usersService->show($this->userWithViewPerm->id);

        $this->assertEquals($this->userWithViewPerm->id, $user->id);
        $this->assertEquals($this->userWithViewPerm->email, $user->email);
        $this->assertEquals($this->userWithViewPerm->firstname, $user->firstname);
        $this->assertEquals($this->userWithViewPerm->lastname, $user->lastname);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToDestroy()
    {
        $this->actingAs($this->userWithViewPerm);

        $this->usersService->destroy($this->userWithNoPerms->id);
    }

    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);

        $user = $this->usersService->destroy($this->userWithNoPerms->id);

        $this->assertEquals($this->userWithNoPerms->id, $user->id);

        try {
            $this->usersService->show($this->userWithNoPerms->id);
            $this->fail('should never run');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //good boy
        }
    }
}
