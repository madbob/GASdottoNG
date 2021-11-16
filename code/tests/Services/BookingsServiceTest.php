<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use App\Exceptions\AuthException;

class BookingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->gas = \App\Gas::factory()->create();
        list($this->sample_supplier, $this->products, $this->sample_order) = $this->initOrder(null);

        /*
            Nota: la gestione dello stato delle prenotazioni consegnate è
            influenzato dalla presenza di almeno un utente che abbia permessi di
            amministrazione dei movimenti contabili.
            Cfr. DeliverBooking::handle()
        */
        $this->userAdmin = $this->createRoleAndUser($this->gas, 'gas.config,movements.admin');

        $this->userWithShippingPerms = $this->createRoleAndUser($this->gas, 'supplier.shippings', $this->sample_supplier);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');

        $this->service = new \App\Services\BookingsService();
    }

    /*
        Lettura dati prenotazione
    */
    public function testReadBooking()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $data['notes_' . $this->sample_order->id] = 'Nota di test';
        $booking = $this->service->readBooking($data, $this->sample_order, $this->userWithBasePerms, false);
        $this->assertNotNull($booking);
        $this->assertEquals($booking->notes, 'Nota di test');
        $this->assertEquals($booking->status, 'pending');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('booked', true), $total);

        $this->actingAs($this->userWithShippingPerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $data['notes_' . $this->sample_order->id] = '';
        $booking = $this->service->readBooking($data, $this->sample_order, $this->userWithBasePerms, false);
        /*
            https://github.com/madbob/GASdottoNG/issues/151
        */
        $this->assertEquals($booking->notes, '');
        $this->assertEquals($booking->status, 'pending');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('booked', true), $total);

        $this->actingAs($this->userWithBasePerms);
        $booking = $this->service->readBooking([], $this->sample_order, $this->userWithBasePerms, false);
        $this->assertNull($booking);
    }

    /*
        Salvataggio prenotazione
    */
    public function testShipping()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);

        $data['action'] = 'booked';
        $this->service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, false);
        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();
        $this->assertNotNull($booking);

        /*
            Una prenotazione consegnata viene marcata come "salvata" (e non come
            "consegnata") finché non viene salvato anche il relativo movimento
            di pagamento
        */
        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $this->service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, true);

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'saved');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('effective', true), $total);

        $movement = \App\Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total);
        $movement->save();
        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);
    }

    /*
        Permessi sbagliati su lettura prenotazione
    */
    public function testPermissionsOnRead()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $this->service->readBooking($data, $this->sample_order, $this->userWithShippingPerms, false);
    }

    /*
        Permessi sbagliati su consegna
    */
    public function testPermissionsOnShipping()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $this->service->readBooking($data, $this->sample_order, $this->userWithBasePerms, false);

        $data['action'] = 'shipped';
        $this->service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, true);
    }

    /*
        Salvataggio prenotazione su ordine aggregato
    */
    public function testMultipleRead()
    {
        list($supplier2, $products2, $order2) = $this->initOrder($this->sample_order);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $booking = $this->service->readBooking($data, $this->sample_order, $this->userWithBasePerms, false);
        list($data2, $booked_count2, $total2) = $this->randomQuantities($products2);
        $booking2 = $this->service->readBooking($data2, $order2, $this->userWithBasePerms, false);

        $aggregate = $this->sample_order->aggregate->fresh();
        $complete_booking = $aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($complete_booking->total_value, $total + $total2);

        $this->actingAs($this->userWithShippingPerms);
        $complete_data = array_merge($data, $data2);
        $complete_data['action'] = 'shipped';
        $this->service->bookingUpdate($complete_data, $this->sample_order->aggregate, $this->userWithBasePerms, true);

        $movement = \App\Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total + $total2);
        $movement->save();

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);

        $booking2 = $booking2->fresh();
        $this->assertEquals($booking2->status, 'shipped');
        $this->assertNotNull($booking2->payment_id);
        $this->assertEquals($booking2->payment->amount, $total2);
    }

    /*
        Salvataggio prenotazione su ordine aggregato, con una prenotazione vuota
    */
    public function testMultipleWithSecondEmpty()
    {
        list($supplier2, $products2, $order2) = $this->initOrder($this->sample_order);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $booking = $this->service->readBooking($data, $this->sample_order, $this->userWithBasePerms, false);

        $complete_booking = $this->sample_order->aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($complete_booking->total_value, $total);

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $this->service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, true);

        $movement = \App\Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total);
        $movement->save();

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);
    }

    public function testKeepBookedQuantities()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->products);
        $data['action'] = 'booked';
        $this->service->bookingUpdate($data, $this->sample_order->aggregate, $this->userWithBasePerms, false);

        $this->actingAs($this->userWithShippingPerms);
        $booked_data = [];
        foreach($data as $index => $d) {
            $booked_data[$d] = 0;
        }
        $booked_data['action'] = 'shipped';
        $this->service->bookingUpdate($booked_data, $this->sample_order->aggregate, $this->userWithBasePerms, true);

        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();
        $this->assertEquals($booking->status, 'saved');
        $this->assertEquals($booking->products->count(), $booked_count);

        foreach($booking->products as $product) {
            $this->assertEquals($product->quantity, $data[$product->product_id]);
            $this->assertEquals($product->delivered, 0);
        }
    }
}
