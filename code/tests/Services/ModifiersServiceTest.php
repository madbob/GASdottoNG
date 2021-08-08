<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\AuthException;

class ModifiersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = \App\Gas::factory()->create();

        $booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);

        $this->user1 = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->user1->addRole($booking_role->id, $this->gas);

        $this->user2 = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->user2->addRole($booking_role->id, $this->gas);

        $this->userAdmin = $this->createRoleAndUser($this->gas, 'gas.config');

        $this->supplier = \App\Supplier::factory()->create();
        $this->userReferrer = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);

        $this->category = \App\Category::factory()->create();
        $this->measure = \App\Measure::factory()->create();

        $this->product = \App\Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $this->aggregate = \App\Aggregate::factory()->create();

        $this->order = \App\Order::factory()->create([
            'aggregate_id' => $this->aggregate->id,
            'supplier_id' => $this->supplier->id,
        ]);
        $this->order->products()->sync([$this->product->id]);

        $this->booking1 = \App\Booking::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user1->id,
        ]);

        \App\BookedProduct::factory()->create([
            'booking_id' => $this->booking1->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $this->booking2 = \App\Booking::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user2->id,
        ]);

        \App\BookedProduct::factory()->create([
            'booking_id' => $this->booking2->id,
            'product_id' => $this->product->id,
            'quantity' => 8,
        ]);

        $this->modifiersService = new \App\Services\ModifiersService();

        Model::reguard();
    }

    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->user1);

        $modifiers = $this->product->applicableModificationTypes();

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $this->product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->modifiersService->update($mod->id, [
                    'value' => 'price',
                    'arithmetic' => 'apply',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'order',
                    'distribution_type' => 'quantity',
                    'threshold' => [20, 10, 0],
                    'amount' => [0.9, 0.92, 0.94],
                ]);

                break;
            }
        }
    }

    public function testThresholdUnitPrice()
    {
        $this->actingAs($this->userReferrer);

        $modifiers = $this->product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $this->product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->modifiersService->update($mod->id, [
                    'value' => 'price',
                    'arithmetic' => 'apply',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'order',
                    'distribution_type' => 'quantity',
                    'threshold' => [20, 10, 0],
                    'amount' => [0.9, 0.92, 0.94],
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $modifiers = $this->order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(count($aggregated_modifiers), 1);

        $without_discount = $this->product->price * (8 + 3);
        $total = 0.92 * (8 + 3);

        foreach($aggregated_modifiers as $ag) {
            $this->assertEquals($ag->amount * -1, $without_discount - $total);
        }
    }

    public function testThresholdQuantity()
    {
        $this->actingAs($this->userReferrer);

        $modifiers = $this->product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $this->product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->modifiersService->update($mod->id, [
                    'arithmetic' => 'sub',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'booking',
                    'value' => 'percentage',
                    'threshold' => [10, 5, 0],
                    'amount' => [10, 5, 0],
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $modifiers = $this->order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(count($aggregated_modifiers), 1);

        $without_discount = $this->product->price * (8 + 3);
        $total = ($this->product->price * 0.05) * 8;

        foreach($aggregated_modifiers as $ag) {
            $this->assertEquals($ag->amount * -1, $total);
        }

        $redux = $this->order->reduxData();
        $this->assertNotEquals($redux->price, 0);

        foreach($this->order->bookings as $booking) {
            $booking->applyModifiers(null, true);
            $product = $booking->products()->first();
            $discount_value = $booking->getValue('modifier:' . $mod->id, true);

            if ($product->quantity < 5) {
                $this->assertEquals($discount_value, 0);
            }
            else if ($product->quantity >= 5 && $product->quantity < 10) {
                $this->assertEquals($discount_value * -1, ($this->product->price * 0.05) * $product->quantity);
            }
            else {
                $this->assertEquals($discount_value * -1, ($this->product->price * 0.10) * $product->quantity);
            }
        }
    }

    public function testOnOrder()
    {
        $this->actingAs($this->userReferrer);

        $test_shipping_value = 50;

        $modifiers = $this->order->applicableModificationTypes();
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $this->order->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->modifiersService->update($mod->id, [
                    'value' => 'absolute',
                    'arithmetic' => 'sum',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'order',
                    'distribution_type' => 'price',
                    'simplified_amount' => $test_shipping_value,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);
        $this->assertEquals($this->order->bookings->count(), 2);

        $redux = $this->order->reduxData();
        $this->assertNotEquals($redux->price, 0);

        foreach($this->order->bookings as $booking) {
            $booking->applyModifiers(null, true);
            $booked_value = $booking->getValue('booked', true);
            $shipping_value = $booking->getValue('modifier:' . $mod->id, true);
            $this->assertEquals(($booked_value * $test_shipping_value) / $redux->price, $shipping_value);
        }
    }

    public function testOnShippingPlace()
    {
        $this->actingAs($this->userAdmin);

        $delivery_1 = \App\Delivery::factory()->create([
            'default' => true,
        ]);

        $delivery_2 = \App\Delivery::factory()->create([
            'default' => false,
        ]);

        $this->user1->preferred_delivery_id = $delivery_1->id;
        $this->user1->save();
        $this->user2->preferred_delivery_id = $delivery_2->id;
        $this->user2->save();

        $test_shipping_value = 10;

        $modifiers = $delivery_2->applicableModificationTypes();
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $delivery_2->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->modifiersService->update($mod->id, [
                    'value' => 'absolute',
                    'arithmetic' => 'sum',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'booking',
                    'simplified_amount' => $test_shipping_value,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $redux = $this->order->reduxData();
        $this->assertNotEquals($redux->price, 0);

        foreach($this->order->bookings as $booking) {
            $mods = $booking->applyModifiers(null, true);
            $booked_value = $booking->getValue('booked', true);
            $shipping_value = $booking->getValue('modifier:' . $mod->id, true);

            if ($booking->user_id == $this->user1->id) {
                $this->assertEquals($shipping_value, 0);
            }
            else {
                $this->assertEquals($shipping_value, $test_shipping_value);
            }
        }
    }
}
