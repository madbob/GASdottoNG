<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use App\Exceptions\AuthException;
use App\Movement;

class BookingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->sample_order = $this->initOrder(null);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');
    }

    private function updateAndFetch($data, $order, $user, $deliver)
    {
        $this->services['bookings']->bookingUpdate($data, $order->aggregate, $user, $deliver);
        return \App\Booking::where('user_id', $user->id)->where('order_id', $order->id)->first();
    }

    /*
        Lettura dati prenotazione
    */
    public function testReadBooking()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $data['notes_' . $this->sample_order->id] = 'Nota di test';
        $booking = $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);
        $this->assertNotNull($booking);
        $this->assertEquals($booking->notes, 'Nota di test');
        $this->assertEquals($booking->status, 'pending');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('booked', true), $total);

        $this->actingAs($this->userWithShippingPerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $data['notes_' . $this->sample_order->id] = '';
        $booking = $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);
        /*
            https://github.com/madbob/GASdottoNG/issues/151
        */
        $this->assertEquals($booking->notes, '');
        $this->assertEquals($booking->status, 'pending');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('booked', true), $total);

        $this->actingAs($this->userWithBasePerms);
        $booking = $this->updateAndFetch([], $this->sample_order, $this->userWithBasePerms, false);
        $this->assertNull($booking);
    }

    /*
        Consegna prenotazione
    */
    public function testShipping()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);

        $data['action'] = 'booked';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);
        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();
        $this->assertNotNull($booking);

        /*
            Una prenotazione consegnata viene marcata come "salvata" (e non come
            "consegnata") finché non viene salvato anche il relativo movimento
            di pagamento
        */
        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, true);

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'saved');
        $this->assertEquals($booking->products()->count(), $booked_count);
        $this->assertEquals($booking->getValue('effective', true), $total);

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total);
        $movement->save();
        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);
    }

    private function pushFriend($master)
    {
        $friends_role = \App\Role::factory()->create(['actions' => 'users.subusers']);
        $master->addRole($friends_role->id, $this->gas);

        $this->actingAs($master);
        $friend = $this->services['users']->storeFriend(array(
            'username' => 'test friend user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ));

        $booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);
        $friend->addRole($booking_role->id, $this->gas);

        return $friend;
    }

    /*
        Consegna prenotazione con amici
    */
    public function testShippingWithFriend()
    {
        $friend = $this->pushFriend($this->userWithBasePerms);

        $this->actingAs($friend);
        list($friend_data, $friend_booked_count, $friend_total) = $this->randomQuantities($this->sample_order->products);
        $friend_data['action'] = 'booked';
        $this->services['bookings']->bookingUpdate($friend_data, $this->sample_order->aggregate, $friend, false);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $data['action'] = 'booked';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);

        $merged_data = [];
        foreach($this->sample_order->products as $prod) {
            $merged_data[$prod->id] = $data[$prod->id] + $friend_data[$prod->id];
        }

        $this->actingAs($this->userWithShippingPerms);
        $merged_data['action'] = 'shipped';
        $this->updateAndFetch($merged_data, $this->sample_order, $this->userWithBasePerms, true);

        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();
        $this->assertNotNull($booking);

        $friend_booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $friend->id)->first();
        $this->assertNotNull($friend_booking);

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'saved');

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total + $friend_total);
        $movement->save();
        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $friend_booking = $friend_booking->fresh();
        $this->assertEquals($friend_booking->status, 'shipped');

        $aggregate_booking = $this->sample_order->aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($aggregate_booking->status, 'shipped');
    }

    /*
        Consegna prenotazione solo con amici
    */
    public function testShippingWithOnlyFriend()
    {
        $friend = $this->pushFriend($this->userWithBasePerms);

        $this->actingAs($friend);
        list($friend_data, $friend_booked_count, $friend_total) = $this->randomQuantities($this->sample_order->products);
        $friend_data['action'] = 'booked';
        $this->services['bookings']->bookingUpdate($friend_data, $this->sample_order->aggregate, $friend, false);

        $this->actingAs($this->userWithShippingPerms);
        $friend_data['action'] = 'shipped';
        $this->updateAndFetch($friend_data, $this->sample_order, $this->userWithBasePerms, true);

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $friend_total);
        $movement->save();

        $aggregate_booking = $this->sample_order->aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($aggregate_booking->status, 'shipped');
    }

    /*
        Permessi sbagliati su lettura prenotazione
    */
    public function testPermissionsOnRead()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $this->updateAndFetch($data, $this->sample_order, $this->userWithShippingPerms, false);
    }

    /*
        Permessi sbagliati su consegna
    */
    public function testPermissionsOnShipping()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);

        $data['action'] = 'shipped';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, true);
    }

    /*
        Salvataggio prenotazione su ordine aggregato
    */
    public function testMultipleRead()
    {
        $order2 = $this->initOrder($this->sample_order);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        list($data2, $booked_count2, $total2) = $this->randomQuantities($order2->products);
        $complete_data = array_merge($data, $data2);

        $complete_data['action'] = 'booked';
        $this->services['bookings']->bookingUpdate($complete_data, $this->sample_order->aggregate, $this->userWithBasePerms, false);

        $this->nextRound();

        $aggregate = $this->sample_order->aggregate->fresh();
        $complete_booking = $aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($complete_booking->getValue('effective', true), $total + $total2);

        $this->actingAs($this->userWithShippingPerms);
        $complete_data['action'] = 'shipped';
        $this->services['bookings']->bookingUpdate($complete_data, $this->sample_order->aggregate, $this->userWithBasePerms, true);

        $this->nextRound();

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total + $total2);
        $movement->save();

        $this->nextRound();

        $booking = \App\Booking::where('user_id', $this->userWithBasePerms->id)->where('order_id', $this->sample_order->id)->first();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);

        $booking2 = \App\Booking::where('user_id', $this->userWithBasePerms->id)->where('order_id', $order2->id)->first();
        $this->assertEquals($booking2->status, 'shipped');
        $this->assertNotNull($booking2->payment_id);
        $this->assertEquals($booking2->payment->amount, $total2);
    }

    /*
        Salvataggio prenotazione su ordine aggregato, con una prenotazione vuota
    */
    public function testMultipleWithSecondEmpty()
    {
        $order2 = $this->initOrder($this->sample_order);

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $booking = $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);

        $complete_booking = $this->sample_order->aggregate->bookingBy($this->userWithBasePerms->id);
        $this->assertEquals($complete_booking->getValue('effective', true), $total);

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, true);

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total);
        $movement->save();

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total);

        /*
            La prenotazione per il secondo ordine non deve esistere e non deve
            esserci nessun movimento contabile a 0
        */
        $second_booking = $order2->userBooking($this->userWithBasePerms);
        $this->assertFalse($second_booking->exists);
        $this->assertEquals(0, Movement::where('amount', 0)->count());
    }

    /*
        Mantenimento stato prenotazioni alla consegna
    */
    public function testKeepBookedQuantities()
    {
        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $data['action'] = 'booked';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);

        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();

        $this->assertEquals($booking->status, 'pending');
        $this->assertEquals($booking->products()->count(), $booked_count);

        foreach($booking->products as $product) {
            $this->assertEquals($product->delivered, 0);
            $this->assertEquals($product->quantity, $data[$product->product->id]);
        }

        $this->actingAs($this->userWithShippingPerms);
        $shipped_data = [];
        foreach($data as $index => $d) {
            $shipped_data[$d] = 0;
        }
        $shipped_data['action'] = 'shipped';
        $this->updateAndFetch($shipped_data, $this->sample_order, $this->userWithBasePerms, true);

        $booking = \App\Booking::where('order_id', $this->sample_order->id)->where('user_id', $this->userWithBasePerms->id)->first();

        $this->assertEquals($booking->status, 'saved');
        $this->assertEquals($booking->products()->count(), $booked_count);

        foreach($booking->products as $product) {
            $this->assertEquals($product->delivered, 0);
            $this->assertEquals($product->quantity, $data[$product->product->id]);
        }

        foreach($booking->products as $product) {
            $this->assertEquals($product->quantity, $data[$product->product_id]);
            $this->assertEquals($product->delivered, 0);
        }
    }

    private function handlingTotalManual($difference)
    {
        $this->gas->setConfig('unmanaged_shipping', '1');
        $this->sample_order->supplier->unmanaged_shipping_enabled = true;
        $this->sample_order->supplier->save();

        $this->actingAs($this->userWithBasePerms);
        list($data, $booked_count, $total) = $this->randomQuantities($this->sample_order->products);
        $data['action'] = 'booked';
        $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, false);

        $this->actingAs($this->userWithShippingPerms);
        $data['action'] = 'shipped';
        $data['manual_total_' . $this->sample_order->id] = $total + $difference;
        $booking = $this->updateAndFetch($data, $this->sample_order, $this->userWithBasePerms, true);

        $this->assertEquals($booking->getValue('effective', true), $total + $difference);
        $this->assertEquals($booking->modifiedValues->count(), 1);
        $this->assertEquals($booking->modifiedValues->first()->modifier->modifierType->id, 'arrotondamento-consegna');
        $this->assertEquals($booking->modifiedValues->first()->effective_amount, $difference);

        $movement = Movement::generate('booking-payment', $this->userWithBasePerms, $this->sample_order->aggregate, $total);
        $movement->save();

        $booking = $booking->fresh();
        $this->assertEquals($booking->status, 'shipped');
        $this->assertNotNull($booking->payment_id);
        $this->assertEquals($booking->payment->amount, $total + $difference);
    }

    /*
        Consegna con totale manuale superiore al prenotato
    */
    public function testManualShippingPlus()
    {
        $this->handlingTotalManual(10);
    }

    /*
        Consegna con totale manuale inferiore al prenotato
    */
    public function testManualShippingMinus()
    {
        $this->handlingTotalManual(-10);
    }

    /*
        I test per prenotazioni fatte da un amico sono fatti in
        ModifiersServiceTest
    */
}
