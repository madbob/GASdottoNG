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
            $this->assertEquals(count($b->products), $booked_count2);

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
                $this->assertEquals(count($p->modifiers), 0);
                $this->assertEquals($p->message, '');
            }
        }

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
}
