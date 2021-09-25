<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class ProductsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = \App\Gas::factory()->create();
        $this->supplier = \App\Supplier::factory()->create();
        $this->category = \App\Category::factory()->create();
        $this->measure = \App\Measure::factory()->create();

        $this->product = \App\Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        Model::reguard();

        $this->productsService = new \App\Services\ProductsService();
    }

    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        $this->productsService->store(array(
            'supplier_id' => $this->supplier->id,
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

    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->productsService->update($this->product->id, array());
    }

    public function testFailsToUpdateByAdmin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->productsService->update($this->product->id, array());
    }

    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithReferrerPerms);
        $this->productsService->update('broken', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->productsService->update($this->product->id, array(
            'name' => 'Another Product',
            'price' => 10,
        ));

        $product = $this->productsService->show($this->product->id);

        $this->assertNotEquals($product->name, $this->product->name);
        $this->assertEquals($product->price, 10);
        $this->assertEquals($this->product->supplier_id, $product->supplier_id);
    }

    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
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

    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
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
