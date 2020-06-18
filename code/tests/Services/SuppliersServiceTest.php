<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

class SuppliersServiceTest extends TestCase
{
    use DatabaseTransactions;

    private $userWithAdminPerm;
    private $userWithReferrerPerms;
    private $userWithNoPerms;
    private $gas;
    private $supplier;
    private $suppliersService;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = factory(\App\Gas::class)->create();
        $this->supplier = factory(\App\Supplier::class)->create();

        $admin_role = \App\Role::create([
            'name' => 'Admin',
            'actions' => 'supplier.add'
        ]);
        $this->userWithAdminPerm = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        $this->userWithAdminPerm->addRole($admin_role, $this->gas);

        $referrer_role = \App\Role::create([
            'name' => 'Referrer',
            'actions' => 'supplier.modify'
        ]);
        $this->userWithReferrerPerms = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        $this->userWithReferrerPerms->addRole($referrer_role, $this->supplier);

        $user_role = \App\Role::create([
            'name' => 'User',
            'actions' => 'supplier.view'
        ]);
        $this->userWithNormalPerms = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        $this->userWithNormalPerms->addRole($user_role, $this->gas);

        $this->userWithNoPerms = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        Model::reguard();

        $this->suppliersService = new \App\Services\SuppliersService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToStore()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->suppliersService->store(array(
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ));
    }

    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $supplier = $this->suppliersService->store(array(
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ));

        $this->assertEquals('Test Supplier', $supplier->name);
        $this->assertEquals('Test Supplier SRL', $supplier->business_name);
        $this->assertEquals(0, $supplier->current_balance_amount);
    }

    public function testNoList()
    {
        $this->actingAs($this->userWithNoPerms);

        $suppliers = $this->suppliersService->list();
        $this->assertCount(0, $suppliers);
    }

    public function testList()
    {
        $this->actingAs($this->userWithNormalPerms);

        $suppliers = $this->suppliersService->list();
        $this->assertCount(1, $suppliers);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToUpdate()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->suppliersService->update($this->supplier->id, array());
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToUpdateByAdmin()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->suppliersService->update($this->supplier->id, array());
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->actingAs($this->userWithNormalPerms);

        $this->suppliersService->update('id', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $supplier = $this->suppliersService->update($this->supplier->id, array(
            'taxcode' => '12345',
            'vat' => '09876',
        ));

        $this->assertNotEquals($supplier->taxcode, $this->supplier->taxcode);
        $this->assertEquals(0, $supplier->current_balance_amount);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToShowInexistent()
    {
        $this->actingAs($this->userWithNormalPerms);

        $this->suppliersService->show('random');
    }

    public function testShow()
    {
        $this->actingAs($this->userWithNormalPerms);

        $supplier = $this->suppliersService->show($this->supplier->id);

        $this->assertEquals($this->supplier->id, $supplier->id);
        $this->assertEquals($this->supplier->name, $supplier->name);
        $this->assertEquals($this->supplier->business_name, $supplier->business_name);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToDestroy()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->suppliersService->destroy($this->supplier->id);
    }

    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);
        $supplier = $this->suppliersService->destroy($this->supplier->id);
        $this->assertNotEquals(null, $supplier->deleted_at);

        $this->actingAs($this->userWithAdminPerm);
        $supplier = $this->suppliersService->destroy($this->supplier->id);

        try {
            $this->suppliersService->show($this->supplier->id);
            $this->fail('should never run');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //good boy
        }
    }
}
