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

        $this->gas = \App\Gas::factory()->create();
        list($this->sample_supplier, $this->products, $this->sample_order) = $this->initOrder(null);

        $this->userWithShippingPerms = $this->createRoleAndUser($this->gas, 'supplier.shippings', $this->sample_supplier);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');

        $this->bookings_service = new \App\Services\BookingsService();
        $this->dynamic_service = new \App\Services\DynamicBookingsService();
    }

    /*
        Lettura dinamica della prenotazione
    */
    public function testSimple()
    {
        $this->actingAs($this->userWithBasePerms);

        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $data['action'] = 'booked';
        $this->bookings_service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, false);

        list($data2, $booked_count2, $total2) = $this->randomQuantities($this->products);
        $data2['action'] = 'booked';
        $ret = $this->dynamic_service->dynamicModifiers($data2, $this->sample_order->aggregate, $this->userWithBasePerms);

        $this->assertEquals(count($ret->bookings), 1);

        foreach($ret->bookings as $b) {
            $this->assertEquals($b->total, $total2);
            $this->assertEquals(count($b->modifiers), 0);
            $this->assertEquals(count($b->products), $booked_count2);

            foreach($b->products as $pid => $p) {
                $target_product = null;
                foreach($this->products as $prod) {
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

        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();
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
        $this->dynamic_service->dynamicModifiers(['action' => 'booked'], $this->sample_order->aggregate, $this->userWithShippingPerms);
    }

    /*
        Lettura dinamica della prenotazione con permessi corretti
    */
    public function testReferrerReads()
    {
        $this->actingAs($this->userWithShippingPerms);
        $ret = $this->dynamic_service->dynamicModifiers(['action' => 'booked'], $this->sample_order->aggregate, $this->userWithBasePerms);
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

        $this->products[0]->min_quantity = 3;
        $this->products[0]->save();

        $data = [
            'action' => 'booked',
            $this->products[0]->id => 2,
        ];

        $ret = $this->dynamic_service->dynamicModifiers($data, $this->sample_order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità inferiore al minimo consentito');
    }


    /*
        Lettura dinamica della prenotazione, vincolo sul multiplo
    */
    public function testConstraintMultiple()
    {
        $this->actingAs($this->userWithBasePerms);

        $this->products[1]->multiple = 2;
        $this->products[1]->save();

        $data = [
            'action' => 'booked',
            $this->products[1]->id => 5,
        ];

        $ret = $this->dynamic_service->dynamicModifiers($data, $this->sample_order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità non multipla del valore consentito');
    }

    /*
        Lettura dinamica della prenotazione, vincolo sul massimo disponibile
    */
    public function testConstraintMaximum()
    {
        $this->actingAs($this->userWithBasePerms);

        $this->products[2]->max_available = 10;
        $this->products[2]->save();

        $data = [
            'action' => 'booked',
            $this->products[2]->id => 11,
        ];

        $ret = $this->dynamic_service->dynamicModifiers($data, $this->sample_order->aggregate, $this->userWithBasePerms);
        $this->contraintOnProduct($ret, 'Quantità superiore alla disponibilità');
    }
}
