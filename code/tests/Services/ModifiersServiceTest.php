<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use App\Booking;
use App\Exceptions\AuthException;
use App\Printers\Order as OrderPrinter;

class ModifiersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    private function localInitOrder()
    {
        $this->order = $this->initOrder(null);
        $this->populateOrder($this->order);
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
                app()->make('ModifiersService')->update($mod->id, []);
                break;
            }
        }
    }

    private function enforceBookingsTotalQuantity($product_id, $total_quantity)
    {
        $missing_quantity = $total_quantity;
        $order = app()->make('OrdersService')->show($this->order->id);

        foreach($order->bookings as $booking) {
            $data = ['action' => 'booked'];

            foreach($booking->products as $booked_product) {
                if ($booked_product->product_id != $product_id) {
                    $data[$booked_product->product_id] = $booked_product->quantity;
                }
                else {
                    if ($missing_quantity > 0) {
                        $quantity = rand(1, $missing_quantity);
                        $data[$booked_product->product_id] = $quantity;
                        $missing_quantity -= $quantity;
                    }
                }
            }

            $this->actingAs($booking->user);
            app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, false);
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
            app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, false);
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

        foreach ($modifiers as $modifier_type) {
            if ($modifier_type->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $modifier_type->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'price',
                    'arithmetic' => 'apply',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'order',
                    'distribution_type' => 'quantity',
                    'threshold' => $thresholds,
                    'amount' => $threshold_prices,
                ]);

                $this->assertNotNull($modifier_type->modifiers->firstWhere('id', $mod->id));

                break;
            }
        }

        $this->assertNotNull($mod);

        foreach([21, 15, 3] as $threshold_index => $total_quantity) {
            $this->nextRound();
            $this->enforceBookingsTotalQuantity($product->id, $total_quantity);
            $this->nextRound();

            $order = app()->make('OrdersService')->show($this->order->id);
            $modifiers = $order->applyModifiers();
            $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
            $this->assertEquals(1, count($aggregated_modifiers));

            $without_discount = $product->price * $total_quantity;
            $total = $threshold_prices[$threshold_index] * $total_quantity;

            foreach($aggregated_modifiers as $ag) {
                $amount_check = round($ag->amount * -1, 3);
                $total_check = round($without_discount - $total, 3);
                $this->assertEquals($amount_check, $total_check);
            }
        }
    }

    /*
        Modificatore applicato su Prodotto con pezzatura, con soglie sul valore
    */
    public function testThresholdUnitPricePortions()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);
        $product = $this->order->products->random();

        $product->portion_quantity = 0.3;
        $product->save();

        $modifiers = $product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        $thresholds = [10, 5, 0];
        $threshold_prices = [1, 2, 3];

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'price',
                    'arithmetic' => 'apply',
                    'scale' => 'major',
                    'applies_type' => 'quantity',
                    'applies_target' => 'product',
                    'threshold' => $thresholds,
                    'amount' => $threshold_prices,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $booking = $order->bookings->random();

        /*
            In prenotazione la quantità è espressa in numero di pezzi, in
            consegna è espressa in peso totale.
            Le soglie dei modificatori devono sempre essere applicate sui pesi
            totali.
        */

        $data = [
            'action' => 'booked',
            $product->id => 3,
        ];
        $this->actingAs($booking->user);
        app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, false);
        $booking = $booking->fresh();

        $mods = $booking->applyModifiersWithFriends(null, false);
        $this->assertEquals(\App\ModifiedValue::count(), 0);
        $this->assertEquals($mods->count(), 1);

        $total_quantity = 3 * 0.3;
        $without_discount = $product->price * $total_quantity;
        $total = $threshold_prices[2] * $total_quantity;

        foreach($mods as $m) {
            $effective_check = round($m->effective_amount * -1, 3);
            $total_check = round($without_discount - $total, 3);
            $this->assertEquals($effective_check, $total_check);
        }

        $this->nextRound();

        $data = [
            'action' => 'booked',
            $product->id => 20,
        ];
        $this->actingAs($booking->user);
        app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, false);
        $booking = $booking->fresh();
        $mods = $booking->applyModifiersWithFriends(null, false);
        $this->assertEquals(\App\ModifiedValue::count(), 0);
        $this->assertEquals($mods->count(), 1);

        $total_quantity = 20 * 0.3;
        $without_discount = $product->price * $total_quantity;
        $total = $threshold_prices[1] * $total_quantity;

        foreach($mods as $m) {
            $this->assertEquals(round($m->effective_amount * -1, 2), round($without_discount - $total, 2));
        }

        $this->nextRound();

        /*
            Da qui agisco in consegna, dunque con le quantità a peso
        */

        $this->actingAs($this->userWithShippingPerms);

        $data = [
            'action' => 'shipped',
            $product->id => 6,
        ];
        app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, true);
        $booking = $booking->fresh();
        $mods = $booking->applyModifiersWithFriends(null, false);
        $this->assertEquals(\App\ModifiedValue::count(), 1);
        $this->assertEquals($mods->count(), 1);

        $total_quantity = 6;
        $without_discount = $product->price * $total_quantity;
        $total = $threshold_prices[1] * $total_quantity;

        foreach($mods as $m) {
            $this->assertEquals(round($m->effective_amount * -1, 2), round($without_discount - $total, 2));
        }

        $this->nextRound();

        $data = [
            'action' => 'shipped',
            $product->id => 4,
        ];
        app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, true);

        $this->nextRound();

        $booking = $booking->fresh();
        $found = false;

        foreach($booking->products as $prod) {
            if ($prod->product_id == $product->id) {
                $found = true;
                $this->assertEquals(4, $prod->delivered);
            }
        }

        $this->assertTrue($found);

        $mods = $booking->applyModifiersWithFriends(null, false);
        $this->assertEquals(\App\ModifiedValue::count(), 1);
        $this->assertEquals($mods->count(), 1);

        $total_quantity = 4;
        $without_discount = $product->price * $total_quantity;
        $total = $threshold_prices[2] * $total_quantity;

        foreach($mods as $m) {
            $this->assertEquals(round($m->effective_amount * -1, 2), round($without_discount - $total, 2));
        }
    }

    /*
        Modificatore applicato su Prodotto, con soglie sulle quantità
    */
    public function testThresholdQuantity()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);
        $booked_product = $this->order->bookings->random()->products->filter(fn($p) => $p->quantity != 0)->random();
        $booked_product->quantity = 7;
        $booked_product->save();
        $product = $booked_product->product;

        $modifiers = $product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
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

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $modifiers = $order->applyModifiers();
        $this->assertEquals(1, $modifiers->count());
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(1, count($aggregated_modifiers));

        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->price, 0);
        $exists = false;

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiersWithFriends($redux, true);
            $booked_product = $booking->products()->where('product_id', $product->id)->first();

            if (is_null($booked_product)) {
                $this->assertEquals($mods->count(), 0);
            }
            else {
                $exists = true;

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

        $this->assertTrue($exists);
    }

    /*
        Modificatore sul Prodotto, con soglia sull'Ordine
    */
    public function testOrderPrice()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);

        $order = app()->make('OrdersService')->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $current_total = $redux->price;

        $booked_product = $order->bookings->random()->products->filter(fn($p) => $p->quantity != 0)->random();
        $product = $booked_product->product;

        $modifiers = $product->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'arithmetic' => 'sub',
                    'scale' => 'major',
                    'applies_type' => 'order_price',
                    'applies_target' => 'product',
                    'value' => 'percentage',
                    'threshold' => [$current_total + 10],
                    'amount' => [5],
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $modifiers = $order->applyModifiers();
        $this->assertEquals(0, $modifiers->count());

        do {
            $other_booked_product = $order->bookings->random()->products->filter(fn($p) => $p->quantity != 0)->random();
        } while($other_booked_product->product_id != $booked_product->product_id);

        $other_booked_product->quantity += 20;
        $other_booked_product->save();

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $modifiers = $order->applyModifiers();
        $aggregated_modifiers = \App\ModifiedValue::aggregateByType($modifiers);
        $this->assertEquals(1, count($aggregated_modifiers));
        $exists = false;

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiersWithFriends($redux, true);
            $booked_product = $booking->products()->where('product_id', $product->id)->first();

            if (is_null($booked_product)) {
                $this->assertEquals(0, $mods->count());
            }
            else {
                $exists = true;
                $this->assertEquals(1, $mods->count());
                $m = $mods->first();
                $this->assertEquals($m->effective_amount * -1, round(($product->price * $booked_product->quantity) * 0.05, 4));
            }
        }

        $this->assertTrue($exists);
    }

    private function reviewBookingsIntoOrder($mod, $test_shipping_value)
    {
        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
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
                app()->make('ModifiersService')->update($mod->id, [
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

    private function simplePercentageMod($reference, $target, $distribution, $amount)
    {
        $modifiers = $reference->applicableModificationTypes();

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $reference->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'percentage',
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
        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->order->id);

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
            app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, true);
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
        app()->make('OrdersService')->fixModifiers($this->order->id, 'adjust');
        $this->reviewBookingsIntoOrder($mod, $test_shipping_value);
    }

    /*
        Modificatore con movimento contabile applicato sull'ordine in base al valore
    */
    public function testOnOrderWithMovement()
    {
        $movements = \App\Movement::all();
        $this->assertEquals(0, $movements->count());

        list($user, $data, $total) = $this->initModifierWithMovement();
        $this->assertEquals($this->order->supplier->currentBalanceAmount(), 0);
        $this->assertEquals($this->gas->currentBalanceAmount(), 0);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        app()->make('BookingsService')->bookingUpdate($data, $this->order->aggregate, $user, true);

        $this->nextRound();

        $this->shipOrder(true);

        $movement = \App\Movement::generate('booking-payment', $user, $this->order->aggregate, 0);
        $movement->save();

        $movements = \App\Movement::all();
        $this->assertEquals(2, $movements->count());

        $this->nextRound();

        $this->travel(1)->hours();

        $this->actingAs($this->userReferrer);
        $order = app()->make('OrdersService')->show($this->order->id);
        app()->make('OrdersService')->fixModifiers($this->order->id, 'adjust');

        $this->nextRound();

        $movements = \App\Movement::all();
        $this->assertEquals(2, $movements->count());
        $booking_payment_found = $donation_found = false;

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals(1, $this->order->bookings->count());

        foreach($order->bookings as $booking) {
            $total = $booking->getValue('delivered', true);
            $total = round($total, 2);
            $total_donation = round($total * 0.1, 2);

            foreach($movements as $mov) {
                if ($mov->type == 'booking-payment') {
                    $this->assertEquals(round($mov->amount, 2), $total);
                    $booking_payment_found = true;
                }
                else if ($mov->type == 'donation-to-gas') {
                    $this->assertEquals(round($mov->amount, 2), $total_donation);
                    $donation_found = true;
                }
                else {
                    throw new \Exception("Tipo di movimento invalido", 1);
                }
            }
        }

        $this->assertTrue($booking_payment_found && $donation_found);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals($order->supplier->currentBalanceAmount(), $total);
        $this->gas = $this->gas->fresh();
        $this->assertEquals($this->gas->currentBalance(defaultCurrency())->gas, $total_donation);

        $this->travelBack();
    }

    private function completeTestWeight()
    {
        $test_shipping_value = 50;

        $mod = $this->simpleMod($this->order, 'order', 'weight', $test_shipping_value);
        $this->assertNotNull($mod);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->relative_price, 0.0);

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);
            $this->assertEquals(1, $mods->count());

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
        Modificatore passivo applicato sulla prenotazione
    */
    public function testOnBooking()
    {
        $this->localInitOrder();
        $this->actingAs($this->userReferrer);

        $test_passive = 10;

        $modifiers = $this->order->applicableModificationTypes();
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $this->order->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'percentage',
                    'arithmetic' => 'passive',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'booking',
                    'simplified_amount' => $test_passive,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        foreach($this->order->bookings as $booking) {
            $modifiers = $booking->applyModifiers(null, true);
            $this->assertEquals($modifiers->count(), 1);

            /*
                Un modificatore passivo ha sempre valore 0, nel totale della
                prenotazione; esiste solo accedendovi direttamente
            */
            $passive_value = $booking->getValue('modifier:' . $mod->id, true);
            $this->assertEquals($passive_value, 0);

            $booked_value = $booking->getValue('booked', true);
            $effective_value = $booking->getValue('effective', true);
            $this->assertEquals($modifiers->first()->effective_amount, ($booked_value * $test_passive) / 100);
            $this->assertEquals($effective_value, $booked_value);
        }
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

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
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

    private function pushFriend($master, $overlap)
    {
        if (is_a($master, Booking::class)) {
            $user = $master->user;
            $booking = $master;
        }
        else {
            $user = $master;
            $booking = null;
        }

        $friends_role = \App\Role::factory()->create(['actions' => 'users.subusers']);
        $user->addRole($friends_role->id, $this->gas);

        $this->actingAs($user);
        $friend = app()->make('UsersService')->storeFriend(array(
            'username' => 'test friend user',
            'firstname' => 'gianni',
            'lastname' => 'giallo',
            'password' => 'password'
        ));

        $friend->addRole($this->booking_role->id, $this->gas);

        $this->actingAs($friend);
        $data = [];

        if ($booking && $overlap) {
            $added = false;

            do {
                foreach($booking->products as $p) {
                    $q = rand(0, 3);
                    if ($q) {
                        $data[$p->product_id] = $q;
                        $added = true;
                    }
                }
            } while($added == false);
        }
        else {
            if ($booking) {
                $booked = $booking->products->map(fn($p) => $p->product_id)->toArray();
            }
            else {
                $booked = [];
            }

            $added = false;

            foreach($this->order->products as $p) {
                if (in_array($p->id, $booked)) {
                    continue;
                }

                $q = rand(1, 5);
                $data[$p->id] = $q;
                $added = true;
            }

            $this->assertTrue($added);
        }

        $this->assertFalse(empty($data));
        $data['action'] = 'booked';
        app()->make('BookingsService')->bookingUpdate($data, $this->order->aggregate, $friend, false);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $order = app()->make('OrdersService')->show($this->order->id);
        $friend_booking = $order->bookings()->where('user_id', $friend->id)->first();
        $this->assertNotNull($friend_booking);

        return [$friend, $friend_booking];
    }

    /*
        Modificatore su un prodotto
    */
    public function testOnProduct()
    {
        $this->localInitOrder();

        $product = $this->order->products->random();

        $this->actingAs($this->userReferrer);
        $test_shipping_value = 2;
        $mod = $this->simpleMod($product, 'product', 'none', $test_shipping_value);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);

        foreach($order->bookings as $booking) {
            $mods = $booking->applyModifiers(null, true);
            $found = false;

            foreach($booking->products as $booked_product) {
                if ($booked_product->product_id == $product->id) {
                    $this->assertEquals($mods->count(), 1);

                    foreach($mods as $mod) {
                        $this->assertEquals($mod->effective_amount, $booked_product->quantity * $test_shipping_value);
                    }

                    $found = true;
                }
            }

            if ($found == false) {
                $this->assertEquals($mods->count(), 0);
            }
        }
    }

    /*
        Prenotazione di un amico insieme alla prenotazione dell'utente padre
    */
    private function testWithFriend($overlap)
    {
        $this->localInitOrder();

        /*
            Pesco una prenotazione, e aggiungo un amico all'utente
        */

        $booking = $this->order->bookings->random();
        $initial_amount = $booking->getValue('effective', true);
        list($friend, $friend_booking) = $this->pushFriend($booking, $overlap);

        /*
            Creo una prenotazione per l'utente
        */

        $this->nextRound();

        $booking = $this->order->bookings()->where('id', $booking->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals($booking->friends_bookings->count(), 1);
        $amount_of_friend = $friend_booking->getValue('effective', true);
        $this->assertNotEquals($amount_of_friend, 0);
        $amount_with_friend = $booking->getValue('effective', true);
        $this->assertEquals($amount_with_friend, $initial_amount + $amount_of_friend);
        $amount_of_friend = $friend_booking->getValue('booked', true);

        /*
            Aggiungo un modificatore
        */

        $this->actingAs($this->userReferrer);
        $test_shipping_value = 10;
        $mod = $this->simplePercentageMod($this->order, 'booking', 'price', $test_shipping_value);

        $this->nextRound();

        $booking = $this->order->bookings()->where('id', $booking->id)->first();
        $mods = $booking->applyModifiers(null, true);
        $this->assertEquals($mods->count(), 1);
        $second_initial_amount = $booking->getValue('booked', true);
        $this->assertTrue($second_initial_amount > 0);
        $this->assertEquals($second_initial_amount, $initial_amount + $amount_of_friend);

        $this->nextRound();

        $booking = $this->order->bookings()->where('id', $booking->id)->first();
        $second_initial_amount = $booking->getValue('effective', true);
        $this->assertEquals(round($second_initial_amount, 2), round(($initial_amount + $amount_of_friend) * 1.10, 2));

        /*
            Formatto l'ordine, e controllo i valori per la prenotazione specifica
        */

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $booking_found = false;
        $shipping_cost_found = false;

        $printer = new OrderPrinter();
        $formatted = $printer->formatShipping($order, splitFields(['lastname', 'firstname', 'name', 'quantity', 'price']), 'booked', false, 'all_by_name', 1);

        foreach($formatted->contents as $d) {
            if ($d->user_id == $booking->user_id) {
                $booking_found = true;
                $mods = $booking->applyModifiersWithFriends(null, false);
                $actual_mods = [];

                foreach($mods as $mod) {
                    if (isset($actual_mods[$mod->modifier->modifierType->name]) == false) {
                        $actual_mods[$mod->modifier->modifierType->name] = 0;
                    }

                    $actual_mods[$mod->modifier->modifierType->name] += $mod->effective_amount;
                }

                foreach($d->totals as $key => $value) {
                    $value = (float) $value;

                    if ($key == 'total') {
                        $this->assertEquals($value, $second_initial_amount);
                    }
                    else {
                        $this->assertEquals(round($actual_mods[$key], 2), round($value, 2));
                        $this->assertEquals(round(($initial_amount + $amount_of_friend) * 0.10, 2), round($value, 2));
                        $shipping_cost_found = true;
                    }
                }

                break;
            }
        }

        $this->assertEquals($booking_found, true);
        $this->assertEquals($shipping_cost_found, true);
    }

    public function testWithFriendOverlap()
    {
        $this->testWithFriend(true);
    }

    public function testWithFriendNoOverlap()
    {
        $this->testWithFriend(false);
    }

    /*
        Prenotazione di un amico, senza prenotazione dell'utente padre
    */
    public function testWithOnlyFriend()
    {
        $this->localInitOrder();

        $this->actingAs($this->userReferrer);
        $test_shipping_value = 10;
        $mod = $this->simplePercentageMod($this->order, 'booking', 'price', $test_shipping_value);

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'users.admin');
        $this->actingAs($this->userWithAdminPerm);
        $newUser = app()->make('UsersService')->store(array(
            'username' => 'test user',
            'firstname' => 'luigi',
            'lastname' => 'verdi',
            'password' => 'password'
        ));

        $newUser->addRole($this->booking_role->id, $this->gas);
        list($friend, $booking) = $this->pushFriend($newUser, false);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $booking_found = false;
        $shipping_cost_found = false;

        $printer = new OrderPrinter();
        $formatted = $printer->formatShipping($order, splitFields(['lastname', 'firstname', 'name', 'quantity', 'price']), 'booked', false, 'all_by_name', 1);

        foreach($formatted->contents as $d) {
            if ($d->user_id == $newUser->id) {
                $booking_found = true;
                $mods = $booking->applyModifiersWithFriends(null, false);
                $actual_mods = [];

                foreach($mods as $mod) {
                    if (isset($actual_mods[$mod->modifier->modifierType->name]) == false) {
                        $actual_mods[$mod->modifier->modifierType->name] = 0;
                    }

                    $actual_mods[$mod->modifier->modifierType->name] += $mod->effective_amount;
                }

                foreach($d->totals as $key => $value) {
                    $value = (float) $value;

                    if ($key == 'total') {
                        $this->assertEquals($value, $booking->getValue('effective', true));
                    }
                    else {
                        $this->assertEquals(round($actual_mods[$key], 2), round($value, 2));
                        $shipping_cost_found = true;
                    }
                }

                break;
            }
        }

        $this->assertEquals($booking_found, true);
        $this->assertEquals($shipping_cost_found, true);
    }

    private function initModifierWithMovement()
    {
        $this->order = $this->initOrder(null);

        $this->actingAs($this->userReferrer);
        $modifiers = $this->order->applicableModificationTypes();
        $this->assertEquals(count($modifiers), 2);
        $mod = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $this->order->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'percentage',
                    'arithmetic' => 'sum',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'booking',
                    'simplified_amount' => 10,
                    'movement_type_id' => 'donation-to-gas',
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);

        $this->nextRound();

        $booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);

        $user = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $user->addRole($booking_role, $this->gas);

        $this->actingAs($user);
        list($data, $booked_count, $total) = $this->randomQuantities($this->order->products);
        $data['action'] = 'booked';
        app()->make('BookingsService')->bookingUpdate($data, $this->order->aggregate, $user, false);

        return [$user, $data, $total];
    }

    /*
        Consegna prenotazione con modificatore che genera movimento contabile
    */
    public function testModifierWithMovement()
    {
        list($user, $data, $total) = $this->initModifierWithMovement();

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        app()->make('BookingsService')->bookingUpdate($data, $this->order->aggregate, $user, true);

        $this->nextRound();

        $booking = $this->order->bookings()->where('user_id', $user->id)->first();
        $amount = $booking->getValue('effective', true);
        $this->assertEquals(round($amount, 2), round($total + ($total * 0.1), 2));

        $this->nextRound();

        $movement = \App\Movement::generate('booking-payment', $user, $this->order->aggregate, $total + ($total * 0.1));
        $movement->save();

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $this->order = app()->make('OrdersService')->show($this->order->id);
        $currency = defaultCurrency();
        $this->assertEquals(round($this->order->supplier->currentBalanceAmount($currency), 2), round($total, 2));
        $this->assertEquals(round($this->gas->currentBalance($currency)->gas, 2), round($total * 0.1, 2));

        $movements = \App\Movement::all();
        $this->assertEquals($movements->count(), 2);
        $booking_payment_found = $donation_found = false;

        foreach($movements as $mov) {
            if ($mov->type == 'booking-payment') {
                $this->assertEquals(round($mov->amount, 2), round($total, 2));
                $booking_payment_found = true;
            }
            else if ($mov->type == 'donation-to-gas') {
                $this->assertEquals(round($mov->amount, 2), round($total * 0.1, 2));
                $donation_found = true;
            }
            else {
                throw new \Exception("Tipo di movimento invalido", 1);
            }
        }

        $this->assertTrue($booking_payment_found && $donation_found);
    }

    /*
        Consegna prenotazione senza quantità con modificatore che genera
        movimento contabile
    */
    public function testManualShippingModifierWithMovement()
    {
        list($user, $data, $total) = $this->initModifierWithMovement();

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $data['manual_total_' . $this->order->id] = 100;
        app()->make('BookingsService')->bookingUpdate($data, $this->order->aggregate, $user, true);

        $this->nextRound();

        $movement = \App\Movement::generate('booking-payment', $user, $this->order->aggregate, 100);
        $movement->save();

        $this->nextRound();

        $movements = \App\Movement::all();
        $this->assertEquals($movements->count(), 2);

        foreach($movements as $mov) {
            if ($mov->type == 'booking-payment') {
                $this->assertEquals(round($mov->amount, 2), 90);
            }
            else if ($mov->type == 'donation-to-gas') {
                $this->assertEquals(round($mov->amount, 2), 10);
            }
            else {
                throw new \Exception("Tipo di movimento invalido", 1);
            }
        }
    }
}
