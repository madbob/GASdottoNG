<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

class ProductsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private $userWithAdminPerm;
    private $userWithReferrerPerms;
    private $userWithNoPerms;
    private $gas;
    private $supplier;
    private $product;
    private $category;
    private $measure;
    private $productsService;

    public function setUp()
    {
        parent::setUp();
        Model::unguard();

        $this->gas = factory(\App\Gas::class)->create();
        $this->supplier = factory(\App\Supplier::class)->create();
        $this->category = factory(\App\Category::class)->create();
        $this->measure = factory(\App\Measure::class)->create();

        $this->product = factory(\App\Product::class)->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $admin_role = \App\Role::create([
            'name' => 'Admin',
            'actions' => 'supplier.add'
        ]);
        $this->userWithAdminPerm = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        $this->userWithAdminPerm->addRole($admin_role, $this->gas);

        $referrer_role = \App\Role::create([
            'name' => 'Admin',
            'actions' => 'supplier.modify'
        ]);
        $this->userWithReferrerPerms = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);
        $this->userWithReferrerPerms->addRole($referrer_role, $this->supplier);

        $this->userWithNoPerms = factory(\App\User::class)->create([
            'gas_id' => $this->gas->id
        ]);

        Model::reguard();

        $this->productsService = new \App\Services\ProductsService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToStore()
    {
        $this->actingAs($this->userWithNoPerms);
        $this->productsService->store(array(
            'supplier_id' => $this->supplier,
            'name' => 'Test Product'
        ));
    }

    public function testStore()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $product = $this->productsService->store(array(
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ));

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals($this->supplier->id, $product->supplier_id);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToUpdate()
    {
        $this->actingAs($this->userWithNoPerms);
        $this->productsService->update($this->product->id, array());
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToUpdateByAdmin()
    {
        $this->actingAs($this->userWithAdminPerm);
        $this->productsService->update($this->product->id, array());
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->actingAs($this->userWithReferrerPerms);
        $this->productsService->update('broken', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);
        $product = $this->productsService->update($this->product->id, array(
            'name' => 'Another Product',
        ));

        $this->assertNotEquals($product->name, $this->product->name);
        $this->assertEquals($this->product->supplier_id, $product->supplier_id);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFailsToShowInexistent()
    {
        $this->actingAs($this->userWithNoPerms);
        $this->productsService->show('random');
    }

    public function testShow()
    {
        $this->actingAs($this->userWithNoPerms);
        $product = $this->productsService->show($this->product->id);

        $this->assertEquals($this->product->id, $product->id);
        $this->assertEquals($this->product->name, $product->name);
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToDestroy()
    {
        $this->actingAs($this->userWithNoPerms);
        $this->productsService->destroy($this->product->id);
    }

    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->productsService->destroy($this->product->id);
        $product = $this->productsService->show($this->product->id);
        $this->assertNotNull($product->deleted_at);
    }
}
