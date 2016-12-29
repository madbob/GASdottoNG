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

        $this->gas = factory(App\Gas::class)->create([
            'id' => 1
        ]);

        $this->userWithViewPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas['id']
        ]);

        factory(App\Permission::class)->create([
            'user_id' => $this->userWithViewPerm['id'],
            'target_id' => $this->gas['id'],
            'action' => 'users.view'
        ]);

        $this->userWithAdminPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas['id']
        ]);

        factory(App\Permission::class)->create([
            'user_id' => $this->userWithAdminPerm['id'],
            'target_id' => $this->gas['id'],
            'action' => 'users.admin'
        ]);

        $this->userWithNoPerms = factory(App\User::class)->create([
            'gas_id' => $this->gas['id']
        ]);

        factory(App\User::class, 3)->create([
            'gas_id' => $this->gas['id']
        ]);

        $this->usersService = new \App\UsersService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToListUsersBecauseNonAuthorized()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->usersService->listUsers();
    }

    public function testList()
    {
        $this->actingAs($this->userWithViewPerm);

        $this->usersService->listUsers();
    }
}
