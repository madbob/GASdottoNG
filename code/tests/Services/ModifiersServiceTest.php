<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use App\Exceptions\AuthException;

class ModifiersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);

        $this->users = \App\User::factory()->count(5)->create(['gas_id' => $this->gas->id]);
        foreach($this->users as $user) {
            $user->addRole($booking_role->id, $this->gas);
        }
    }

    private function localInitOrder()
    {
        $this->order = $this->initOrder(null);

        foreach($this->users as $user) {
            $this->actingAs($user);
            list($data, $booked_count, $total) = $this->randomQuantities($this->order->products);
            $data['action'] = 'booked';
            $this->services['bookings']->bookingUpdate($data, $this->order->aggregate, $user, false);
        }
    }

    /*
        Creazione Modificatore con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);

        $this->localInitOrder();
        $this->actingAs($this->users->first());

        $product = $this->order->products->random();
        $modifiers = $product->applicableModificationTypes();

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->services['modifiers']->update($mod->id, []);
                break;
            }
        }
    }

    private function enforceBookingsTotalQuantity($product_id, $total_quantity)
    {
        $missing_quantity = $total_quantity;
        $order = $this->services['orders']->show($this->order->id);

        foreach($order->bookings as $booking) {
            $data = ['action' => 'booked'];

            foreach($booking->products as $booked_product) {
                if ($booked_product->product_id != $product_id) {
                    $data[$booked_product->product_id] = $booked_product->quantity;
                }
                else {
                    if ($missing_quantity > 0) {
                        $quantity = rand(0, $missing_quantity);
                        $data[$booked_product->product_id] = $quantity;
                        $missing_quantity -= $quantity;
                    }
                }
            }

            $this->actingAs($booking->user);
            $this->services['bookings']->bookingUpdate($data, $order->aggregate, $booking->user, false);
        }

        if ($missing_quantity > 0) {
            $data = ['action' => 'booked'];

            $booking = $order->bookings()->first();
            $found = false;

            foreach($booking->products as $booked_product) {
                if ($booked_product->product_id != $product_id) {
                    $data[$booked_product->product_id] = $booked_product->quantity;
                }
                else {
                    $data[$booked_product->product_id] = $missing_quantity + $booked_product->quantity;
                    $found = true;
                }
            }

            if ($found == false) {
                $data[$product_id] = $missing_quantity;
            }

            $this->actingAs($booking->user);
            $this->services['bookings']->bookingUpdate($data, $order->aggregate, $booking->user, false);
        }

        $booked_quantity = \App\BookedProduct::where('product_id', $product_id)->sum('quantity');
        $this->assertEquals($booked_quantity, $total_quantity);
    }

    /*
        Modificatore applicato su Prodotto, con soglie sul valore
    */
    public function testThresholdUnitPrice()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);
        $product = $this->order->products->random();

        $modifiers = $product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        $thresholds = [20, 10, 0];
        $threshold_prices = [0.9, 0.92, 0.94];

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->services['modifiers']->update($mod->id, [
                    'value' => 'price',
                    'arithmetic' => 'apply',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'order',
                    'distribution_type' => 'quantity',
                    'threshold' => $thresholds,
                    'amount' => $threshold_prices,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        foreach([21, 15, 3] as $threshold_index => $total_quantity) {
            $this->enforceBookingsTotalQuantity($product->id, $total_quantity);

            $order = $this->services['orders']->show($this->order->id);
            $modifiers = $order->applyModifiers();
            $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
            $this->assertEquals(count($aggregated_modifiers), 1);

            $without_discount = $product->price * $total_quantity;
            $total = $threshold_prices[$threshold_index] * $total_quantity;

            foreach($aggregated_modifiers as $ag) {
                $this->assertEquals($ag->amount * -1, $without_discount - $total);
            }
        }
    }

    /*
        Modificatore applicato su Prodotto, con soglie sulle quantità
    */
    public function testThresholdQuantity()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);
        $product = $this->order->products->random();

        $modifiers = $product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->services['modifiers']->update($mod->id, [
                    'arithmetic' => 'sub',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'product',
                    'value' => 'percentage',
                    'threshold' => [10, 5, 0],
                    'amount' => [10, 5, 0],
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $order = $this->services['orders']->show($this->order->id);
        $modifiers = $order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(count($aggregated_modifiers), 1);

        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->price, 0);

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);
            $booked_product = $booking->products()->where('product_id', $product->id)->first();

            if (is_null($booked_product)) {
                $this->assertEquals($mods->count(), 0);
            }
            else {
                if ($booked_product->quantity <= 5) {
                    $this->assertEquals($mods->count(), 0);
                }
                else {
                    $this->assertEquals($mods->count(), 1);
                    $m = $mods->first();

                    if ($booked_product->quantity > 5 && $booked_product->quantity <= 10) {
                        $this->assertEquals($m->effective_amount * -1, round(($product->price * $booked_product->quantity) * 0.05, 4));
                    }
                    else {
                        $this->assertEquals($m->effective_amount * -1, round(($product->price * $booked_product->quantity) * 0.10, 4));
                    }
                }
            }
        }
    }

    private function reviewBookingsIntoOrder($mod, $test_shipping_value)
    {
        $order = $this->services['orders']->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->relative_price, 0.0);

        foreach($order->bookings as $booking) {
            if ($booking->status == 'pending') {
                $booked_value = $booking->getValue('booked', true);
            }
            else {
                $booked_value = $booking->getValue('delivered', true);
            }

            $mods = $booking->applyModifiers($redux, true);
            $this->assertEquals($mods->count(), 1);

            foreach($mods as $m) {
                $this->assertEquals(round(($booked_value * $test_shipping_value) / $redux->relative_price, 4), $m->effective_amount);
            }
        }
    }

    private function simpleMod($reference, $target, $distribution, $amount)
    {
        $modifiers = $reference->applicableModificationTypes();

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $reference->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->services['modifiers']->update($mod->id, [
                    'value' => 'absolute',
                    'arithmetic' => 'sum',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => $target,
                    'distribution_type' => $distribution,
                    'simplified_amount' => $amount,
                ]);

                return $mod;
            }
        }

        return null;
    }

    private function shipOrder($random)
    {
        $this->actingAs($this->userWithShippingPerms);
        $order = $this->services['orders']->show($this->order->id);

        foreach($order->bookings as $booking) {
            $data = [];

            foreach($booking->products as $product) {
                if ($random) {
                    $data[$product->product_id] = max($product->quantity + rand(-5, 5), 0);
                }
                else {
                    $data[$product->product_id] = $product->quantity;
                }
            }

            $data['action'] = 'shipped';
            $this->services['bookings']->bookingUpdate($data, $order->aggregate, $booking->user, true);
        }

        $this->actingAs($this->userReferrer);
    }

    /*
        Modificatore applicato sull'ordine in base al valore
    */
    public function testOnOrder()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);

        $test_shipping_value = 50;
        $mod = $this->simpleMod($this->order, 'order', 'price', $test_shipping_value);
        $this->assertNotNull($mod);

        $this->reviewBookingsIntoOrder($mod, $test_shipping_value);

        $this->shipOrder(false);

        $this->order->status = 'shipped';
        $this->order->save();

        $this->reviewBookingsIntoOrder($mod, $test_shipping_value);

        /*
            Alterando le quantità consegnate e forzando il ricalcolo dei
            modificatori, questi devono essere coerenti con le nuove quantità
        */

        $this->shipOrder(true);
        $this->actingAs($this->userReferrer);
        $this->services['orders']->fixModifiers($this->order->id, 'adjust');
        $this->reviewBookingsIntoOrder($mod, $test_shipping_value);
    }

    private function completeTestWeight()
    {
        $test_shipping_value = 50;

        $mod = $this->simpleMod($this->order, 'order', 'weight', $test_shipping_value);
        $this->assertNotNull($mod);

        $order = $this->services['orders']->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->relative_price, 0.0);

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);
            $this->assertEquals($mods->count(), 1);

            $booked_value = $booking->getValue('weight', true);

            foreach($mods as $m) {
                $this->assertEquals(round(($booked_value * $test_shipping_value) / $redux->relative_weight, 4), $m->effective_amount);
            }
        }
    }

    /*
        Modificatore applicato sull'ordine in base al peso
    */
    public function testDistributeOnWeight()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);

        foreach($this->order->products as $product) {
            $product->weight = rand(0.1, 1.5);
            $product->save();
        }

        $this->completeTestWeight();
    }

    /*
        Modificatore applicato sull'ordine in base al peso assoluto (unità di misura non discrete)
    */
    public function testDistributeOnAbsoluteWeight()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);

        for($i = 0; $i < $this->order->products->count() / 3; $i++) {
            $product = $this->order->products->random();
            $product->measure->discrete = false;
            $product->measure->save();
        }

        $this->completeTestWeight();
    }

    /*
        Modificatore applicato su Luogo di Consegna
    */
    public function testOnShippingPlace()
    {
        $this->localInitOrder();
        $this->actingAs($this->userAdmin);

        $delivery_1 = \App\Delivery::factory()->create([
            'default' => true,
        ]);

        $delivery_2 = \App\Delivery::factory()->create([
            'default' => false,
        ]);

        $delivery = [$delivery_1, $delivery_2];

        foreach($this->users as $user) {
            $user->preferred_delivery_id = $delivery[rand(0, 1)]->id;
            $user->save();
        }

        $test_shipping_value = 10;
        $mod = $this->simpleMod($delivery_2, 'booking', 'none', $test_shipping_value);
        $this->assertNotNull($mod);

        $order = $this->services['orders']->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->price, 0.0);

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);

            if ($booking->user->preferred_delivery_id == $delivery_1->id) {
                $this->assertEquals($mods->count(), 0);
            }
            else {
                $this->assertEquals($mods->count(), 1);

                foreach($mods as $m) {
                    $this->assertEquals($m->effective_amount, $test_shipping_value);
                    $this->assertEquals($m->modifier_id, $mod->id);
                }
            }
        }
    }
}
