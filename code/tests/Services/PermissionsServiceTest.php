<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PermissionsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private $permissionsService;
    private $userWithViewPerm;
    private $gas;
    private $userWithGasPermissionPerm;
    private $userWithSupplierModifyPerm;

    public function setUp()
    {
        parent::setUp();

        //parent::enabledQueryDump();

        $this->gas = factory(App\Gas::class)->create();

        $this->userWithViewPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        factory(App\Permission::class)->create([
            'user_id' => $this->userWithViewPerm->id,
            'target_id' => $this->gas->id,
            'action' => 'users.view'
        ]);

        $this->userWithGasPermissionPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        factory(App\Permission::class)->create([
            'user_id' => $this->userWithGasPermissionPerm->id,
            'target_id' => $this->gas->id,
            'action' => 'gas.permissions'
        ]);

        $this->userWithSupplierModifyPerm = factory(App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        factory(App\Permission::class)->create([
            'user_id' => $this->userWithSupplierModifyPerm->id,
            'target_id' => $this->gas->id,
            'action' => 'supplier.modify'
        ]);

        $this->permissionsService = new \App\Services\PermissionsService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testShowForSubjectNoUser()
    {
        $this->permissionsService->showForSubject($this->gas->id, 'users.view');
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testShowForSubjectUserWithoutPerm()
    {
        $this->actingAs($this->userWithViewPerm);

        $this->permissionsService->showForSubject($this->gas->id, 'users.view');
    }

    public function testShowForSubject()
    {
        $this->actingAs($this->userWithSupplierModifyPerm);

        $result = $this->permissionsService->showForSubject($this->gas->id, 'users.view');

        $this->assertEquals('selected', $result['behaviour']);
        $this->assertCount(1, $result['users']);
        $this->assertEquals($this->userWithViewPerm->id, $result['users'][0]->id);
    }

}
