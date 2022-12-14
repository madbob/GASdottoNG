<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use App\Exceptions\AuthException;

class DynamicBookingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = $this->initOrder(null);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');
    }

    /*
        Lettura dinamica della prenotazione
    */
    public function testSimple()
    {
        $this->actingAs($this->userWithBasePerms);

        list($data, $booked_count, $total) = $this->randomQuantities($this->order->products);
        $data['action'] = 'booked';
        $this->services['bookings']->bookingUpdate($data, $this->order->aggregate, $this->userWithBasePerms, false);

        $this->nextRound();

        list($data2, $booked_count2, $total2) = $this->randomQuantities($this->order->products);
        $data2['action'] = 'booked';
        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data2, $this->order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals($b->total, $total2);
            $this->assertEquals(count($b->modifiers), 0);

            $this->assertEquals(count(array_filter($b->products, function($p) {
                return $p->quantity != 0;
            })), $booked_count2);

            foreach($b->products as $pid => $p) {
                $target_product = null;
                foreach($this->order->products as $prod) {
                    if ($prod->id == $pid) {
                        $target_product = $prod;
                        break;
                    }
                }

                $this->assertEquals($p->total, $target_product->price * $data2[$pid] ?? 0);
                $this->assertEquals($p->quantity, $data2[$pid] ?? 0);
                $this->assertEquals(count($p->variants), 0);
                $this->assertEquals($p->message, '');
            }
        }

        $this->nextRound();

        $booking = \App\Booking::where('order_id', $this->order->id)->where('user_id', $this->userWithBasePerms->id)->first();
        $this->assertEquals($booking->getValue('effective', true), $total);
        $this->assertEquals($booking->products()->count(), $booked_count);
    }

    /*
        Lettura dinamica della prenotazione con permessi sbagliati
    */
    public function testFailsToRead()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithBasePerms);
        $this->services['dynamic_bookings']->dynamicModifiers(['action' => 'booked'], $this->order->aggregate, $this->userWithShippingPerms);
    }

    /*
        Lettura dinamica della prenotazione con permessi corretti
    */
    public function testReferrerReads()
    {
        $this->actingAs($this->userWithShippingPerms);
        $ret = $this->services['dynamic_bookings']->dynamicModifiers(['action' => 'booked'], $this->order->aggregate, $this->userWithBasePerms);
        $this->assertEquals(count($ret->bookings), 0);
    }

    /*
        Lettura dinamica delle prenotazioni, prodotti con pezzatura
    */
    public function testPortions()
    {
        $this->actingAs($this->userWithBasePerms);

        $product = $this->order->products->random();
        $product->portion_quantity = 0.3;
        $product->save();

        $data = [
            'action' => 'booked',
            $product->id => 2,
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals(count($b->products), 1);
            $this->assertEquals($b->total, round($product->price * 0.3 * 2, 2));

            foreach($b->products as $pid => $p) {
                $this->assertEquals($p->quantity, 2);
                $this->assertEquals($p->total, round($product->price * 0.3 * 2, 2));
            }
        }
    }

    private function contraintOnProduct($ret, $expected_message)
    {
        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals(count($b->products), 1);

            foreach($b->products as $pid => $p) {
                $this->assertEquals($p->quantity, 0);
                $this->assertEquals($p->message, $expected_message);
            }
        }
    }

    /*
        Lettura dinamica della prenotazione, vincolo sul minimo
    */
    public function testConstraintMinimum()
    {
        $this->actingAs($this->userWithBasePerms);

        $product = $this->order->products->random();
        $product->min_quantity = 3;
        $product->save();

        $data = [
            'action' => 'booked',
            $product->id => 2,
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità inferiore al minimo consentito');
    }


    /*
        Lettura dinamica della prenotazione, vincolo sul multiplo
    */
    public function testConstraintMultiple()
    {
        $this->actingAs($this->userWithBasePerms);

        $product = $this->order->products->random();
        $product->multiple = 2;
        $product->save();

        $data = [
            'action' => 'booked',
            $product->id => 5,
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità non multipla del valore consentito');
    }

    /*
        Lettura dinamica della prenotazione, vincolo sul massimo disponibile
    */
    public function testConstraintMaximum()
    {
        $this->actingAs($this->userWithBasePerms);

        $product = $this->order->products->random();
        $product->max_available = 10;
        $product->save();

        $data = [
            'action' => 'booked',
            $product->id => 11,
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità superiore alla disponibilità');
    }

    /*
        Lettura dinamica di una consegna manuale
    */
    public function testManualShipping()
    {
        $this->actingAs($this->userWithBasePerms);

        list($data, $booked_count, $total) = $this->randomQuantities($this->order->products);
        $data['action'] = 'booked';
        $this->services['bookings']->bookingUpdate($data, $this->order->aggregate, $this->userWithBasePerms, false);

        $this->nextRound();

        $booking = $this->order->userBooking($this->userWithBasePerms);
        $actual_total = $booking->getValue('effective', true);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);

        $data['action'] = 'shipped';
        $data['manual_total_' . $this->order->id] = 100;
        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(1, count($ret->bookings));

        foreach($ret->bookings as $b) {
            $this->assertEquals($b->total, 100);

            $this->assertEquals(count($b->modifiers), 1);
            foreach($b->modifiers as $m) {
                $this->assertEquals(100, $actual_total + $m->amount);
            }
        }
    }

    private function attachDiscount($product)
    {
        $this->actingAs($this->userReferrer);

        $mod = null;
        $modifiers = $product->applicableModificationTypes();
        foreach ($modifiers as $mod) {
            if ($mod->id == 'sconto') {
                $mod = $product->modifiers()->where('modifier_type_id', $mod->id)->first();
                $this->services['modifiers']->update($mod->id, [
                    'value' => 'percentage',
                    'arithmetic' => 'sub',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'product',
                    'simplified_amount' => 10,
                ]);

                break;
            }
        }

        $this->assertNotNull($mod);
        $this->nextRound();
    }

    /*
        Lettura dinamica delle prenotazioni, prodotto con modificatori
    */
    public function testModifiers()
    {
        $product = $this->order->products->random();
        $this->attachDiscount($product);

        $this->actingAs($this->userWithBasePerms);

        $data = [
            'action' => 'booked',
            $product->id => 2,
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals(count($b->products), 1);
            $this->assertEquals($b->total, $product->price * 2 - ($product->price * 0.10 * 2));
            $this->assertEquals(count($b->modifiers), 1);

            foreach($b->products as $pid => $p) {
                $this->assertEquals($p->quantity, 2);
                $this->assertEquals($p->total, $product->price * 2);
            }
        }
    }

    /*
        Lettura dinamica delle prenotazioni, prodotto con varianti e modificatori
    */
    public function testModifiersAndVariants()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->products->random();

        $variant = $this->services['variants']->store([
            'product_id' => $product->id,
            'name' => 'Colore',
            'id' => ['', '', ''],
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);

        $this->nextRound();

        $this->attachDiscount($product);

        $this->actingAs($this->userWithBasePerms);

        $data = [
            'action' => 'booked',
            $product->id => 0,
            'variant_quantity_' . $product->id => [2],
            'variant_selection_' . $variant->id => [$variant->values()->first()->id],
        ];

        $ret = $this->services['dynamic_bookings']->dynamicModifiers($data, $this->order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals(count($b->products), 1);
            $this->assertEquals($b->total, $product->price * 2 - ($product->price * 0.10 * 2));
            $this->assertEquals(count($b->modifiers), 1);

            foreach($b->products as $pid => $p) {
                $this->assertEquals($p->quantity, 2);
                $this->assertEquals($p->total, $product->price * 2);
            }
        }
    }
}
