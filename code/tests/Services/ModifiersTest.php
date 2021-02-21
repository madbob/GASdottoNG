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

    public function testThreshold()
    {
        Model::unguard();

        factory(\App\Modifier::class)->create([
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

        Model::reguard();

        $modifiers = $this->order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(count($aggregated_modifiers), 1);

        $without_discount = $this->product->price * (8 + 3);
        $total = 0.92 * (8 + 3);

        foreach($aggregated_modifiers as $ag) {
            $this->assertEquals($ag->amount * -1, $without_discount - $total);
        }
    }

    public function testOnOrder()
    {
        Model::unguard();

        $test_shipping_value = 50;

        $modifier = factory(\App\Modifier::class)->create([
            'modifier_type_id' => 'spese-trasporto',
            'target_type' => get_class($this->order),
            'target_id' => $this->order->id,
            'value' => 'absolute',
            'arithmetic' => 'sum',
            'scale' => 'minor',
            'applies_type' => 'none',
            'applies_target' => 'order',
            'distribution_type' => 'price',
            'definition' => '[{"threshold":9223372036854775807,"amount":"' . $test_shipping_value . '"}]'
        ]);

        Model::reguard();

        $redux = $this->order->reduxData();

        foreach($this->order->bookings as $booking) {
            $booking->applyModifiers(null, true);
            $booked_value = $booking->getValue('booked', true);
            $shipping_value = $booking->getValue('modifier:' . $modifier->id, true);
            $this->assertEquals(($booked_value * $test_shipping_value) / $redux->price, $shipping_value);
        }
    }
}
