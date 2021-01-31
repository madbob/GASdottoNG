<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

class ModifiersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = factory(\App\Gas::class)->create();

        $booking_role = \App\Role::create([
            'name' => 'Booking',
            'actions' => 'supplier.book'
        ]);

        $this->user1 = factory(\App\User::class)->create(['gas_id' => $this->gas->id]);
        $this->user1->addRole($booking_role, $this->gas);

        $this->user2 = factory(\App\User::class)->create(['gas_id' => $this->gas->id]);
        $this->user2->addRole($booking_role, $this->gas);

        $this->supplier = factory(\App\Supplier::class)->create();
        $this->category = factory(\App\Category::class)->create();
        $this->measure = factory(\App\Measure::class)->create();

        $this->product = factory(\App\Product::class)->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $this->modifier = factory(\App\Modifier::class)->create([
            'modifier_type_id' => 'sconto',
            'target_type' => get_class($this->product),
            'target_id' => $this->product->id,
            'value' => 'price',
            'arithmetic' => 'apply',
            'scale' => 'major',
            'applies_type' => 'quantity',
            'applies_target' => 'order',
            'distribution_type' => 'quantity',
            'definition' => '[{"threshold":"20","amount":"0.9"},{"threshold":"10","amount":"0.92"},{"threshold":0,"amount":"0.94"}]'
        ]);

        $this->aggregate = factory(\App\Aggregate::class)->create();

        $this->order = factory(\App\Order::class)->create([
            'aggregate_id' => $this->aggregate->id,
            'supplier_id' => $this->supplier->id,
        ]);
        $this->order->products()->sync([$this->product->id]);

        $this->booking1 = factory(\App\Booking::class)->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user1->id,
        ]);

        factory(\App\BookedProduct::class)->create([
            'booking_id' => $this->booking1->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $this->booking2 = factory(\App\Booking::class)->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user2->id,
        ]);

        factory(\App\BookedProduct::class)->create([
            'booking_id' => $this->booking2->id,
            'product_id' => $this->product->id,
            'quantity' => 8,
        ]);

        Model::reguard();
    }

    public function testValues()
    {
        $modifiers = $this->order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);

        $this->assertEquals(count($aggregated_modifiers), 1);

        $without_discount = $this->product->price * (8 + 3);
        $total = 0.92 * (8 + 3);

        foreach($aggregated_modifiers as $ag) {
            $this->assertEquals($ag->amount * -1, $without_discount - $total);
        }
    }
}
