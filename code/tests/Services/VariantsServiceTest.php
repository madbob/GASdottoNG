<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

class VariantsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
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

        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = factory(\App\User::class)->create(['gas_id' => $this->gas->id]);

        Model::reguard();

        $this->service = new \App\Services\VariantsService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToStore()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->service->store([
            'product_id' => $this->product->id,
            'name' => 'Colore',
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);
    }

    public function testStore()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $variant = $this->service->store([
            'product_id' => $this->product->id,
            'name' => 'Colore',
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);

        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(3, $variant->values()->count());
        $this->assertEquals(3, $this->product->variant_combos->count());

        $variant = $this->service->store([
            'variant_id' => $variant->id,
            'name' => 'Colore',
            'value' => ['Rosso', 'Verde', 'Blu', 'Giallo'],
        ]);

        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(4, $variant->values()->count());
        $this->assertEquals(4, $this->product->variant_combos->count());

        $variant = $this->service->store([
            'product_id' => $this->product->id,
            'name' => 'Taglia',
            'value' => ['S', 'M', 'L'],
        ]);

        $this->assertEquals(12, $this->product->variant_combos->count());
    }
}
