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

        $booking_role = \App\Role::create([
            'name' => 'Booking',
            'actions' => 'supplier.book'
        ]);

        $this->user1 = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->user1->addRole($booking_role, $this->gas);

        $this->user2 = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->user2->addRole($booking_role, $this->gas);

        $this->supplier = \App\Supplier::factory()->create();

        $referrer_role = \App\Role::create([
            'name' => 'Referrer',
            'actions' => 'supplier.modify'
        ]);

        $this->userReferrer = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userReferrer->addRole($referrer_role, $this->supplier);

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

    public function testThreshold()
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

        foreach($this->order->bookings as $booking) {
            $booking->applyModifiers(null, true);
            $booked_value = $booking->getValue('booked', true);
            $shipping_value = $booking->getValue('modifier:' . $mod->id, true);
            $this->assertEquals(($booked_value * $test_shipping_value) / $redux->price, $shipping_value);
        }
    }
}
