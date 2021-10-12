<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\AuthException;

class VariantsServiceTest extends TestCase
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

        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        Model::reguard();

        $this->service = new \App\Services\VariantsService();
    }

    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
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
            'id' => ['', '', ''],
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);

        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(3, $variant->values()->count());
        $this->assertEquals(3, $this->product->variant_combos->count());

        $old_value = $variant->values()->where('value', 'Rosso')->first();

        $variant = $this->service->store([
            'variant_id' => $variant->id,
            'name' => 'Colore',
            'id' => [
                $variant->values()->where('value', 'Rosso')->first()->id,
                $variant->values()->where('value', 'Verde')->first()->id,
                $variant->values()->where('value', 'Blu')->first()->id,
                ''
            ],
            'value' => ['Rosso', 'Verde', 'Blu', 'Giallo'],
        ]);

        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(4, $variant->values()->count());
        $this->assertEquals(4, $this->product->variant_combos->count());

        $new_value = $variant->values()->where('value', 'Rosso')->first();
        $this->assertEquals($old_value->id, $new_value->id);

        $variant = $this->service->store([
            'product_id' => $this->product->id,
            'name' => 'Taglia',
            'id' => ['', '', ''],
            'value' => ['S', 'M', 'L'],
        ]);

        $this->assertEquals(12, $this->product->variant_combos->count());
    }
}
