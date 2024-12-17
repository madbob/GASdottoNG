<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FastBookingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sample_order = $this->initOrder(null);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book,users.subusers');
    }

    /*
        Consegne veloci
    */
    public function test_fast_shipping()
    {
        $this->populateOrder($this->sample_order);

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $order->aggregate, null);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        $this->assertTrue($order->bookings->count() > 0);

        foreach ($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'shipped');
            $this->assertNotNull($booking->payment);

            foreach ($booking->products as $product) {
                $this->assertEquals($product->quantity, $product->delivered);
            }

            $this->assertEquals($booking->payment->amount, $booking->getValue('effective', true));
        }

        $summary = $order->aggregate->reduxData();
        $this->assertTrue($summary->price_delivered > 0);
    }

    /*
        Consegne veloci parziali
    */
    public function test_fast_shipping_filtered()
    {
        $this->populateOrder($this->sample_order);

        $this->nextRound();

        $filter[$this->sample_order->bookings->random()->user_id] = ['date' => date('Y-m-d'), 'method' => 'credit'];
        $filter[$this->sample_order->bookings->random()->user_id] = ['date' => date('Y-m-d'), 'method' => 'cash'];
        $users = array_keys($filter);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, $filter);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);

        foreach ($order->bookings as $booking) {
            if (in_array($booking->user_id, $users)) {
                $this->assertEquals($booking->status, 'shipped');
                $this->assertNotNull($booking->payment);

                foreach ($booking->products as $product) {
                    $this->assertEquals($product->quantity, $product->delivered);
                }

                $this->assertEquals($booking->payment->amount, $booking->getValue('effective', true));
                $this->assertEquals($booking->payment->method, $filter[$booking->user_id]['method']);
            }
            else {
                $this->assertEquals($booking->status, 'pending');
                $this->assertNull($booking->payment);
            }
        }
    }

    /*
        Consegne veloci con amici
    */
    public function test_fast_shipping_with_friend()
    {
        $friend = $this->createFriend($this->userWithBasePerms);
        $this->actingAs($friend);

        \Log::debug('qui: ' . $friend->id);

        [$data_friend, $booked_count_friend, $total_friend] = $this->randomQuantities($this->sample_order->products);
        $data_friend['action'] = 'booked';
        app()->make('BookingsService')->bookingUpdate($data_friend, $this->sample_order->aggregate, $friend, false);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $order->aggregate, null);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        $this->assertEquals(2, $order->bookings->count());
        $this->assertEquals(1, count($order->topLevelBookings()));

        foreach ($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'shipped');

            foreach ($booking->products as $product) {
                if ($booking->user->isFriend()) {
                    $this->assertEquals(0, $product->delivered);
                }
                else {
                    $this->assertEquals(0, $product->quantity);
                    $this->assertEquals($data_friend[$product->product_id], $product->delivered);
                }
            }

            if ($booking->user->isFriend()) {
                $this->assertNull($booking->payment);
            }
            else {
                $this->assertEquals($booking->payment->amount, $booking->getValue('effective', true));
            }
        }

        $summary = $order->aggregate->reduxData();
        $this->assertTrue($summary->price_delivered > 0);
    }

    /*
        Consegne veloci su prenotazioni salvate
    */
    public function test_fast_shipping_pre_saved()
    {
        $this->populateOrder($this->sample_order);

        $this->nextRound();
        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->sample_order->id);

        $new_data = [];

        foreach ($order->bookings as $booking) {
            [$data, $booked_count, $total] = $this->randomQuantities($order->products);
            $data['action'] = 'saved';
            app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, true);
            $new_data[$booking->user->id] = $data;
        }

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        foreach ($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'saved');
        }

        $this->nextRound();
        $this->actingAs($this->userWithShippingPerms);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, null);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);

        foreach ($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'shipped');
            $this->assertNotNull($booking->payment);
            $data = $new_data[$booking->user->id];

            foreach ($booking->products as $product) {
                if (isset($data[$product->product_id])) {
                    $this->assertEquals($data[$product->product_id], $product->delivered);
                }
                else {
                    $this->assertEquals(0, $product->delivered);
                }
            }

            $this->assertEquals($booking->payment->amount, $booking->getValue('effective', true));
        }
    }

    /*
        Consegne veloci e generazione ricevute
    */
    public function test_fast_shipping_receipts()
    {
        $this->gas->setConfig('extra_invoicing', [
            'business_name' => 'Test',
            'taxcode' => '0123456789',
            'vat' => '0123456789',
            'address' => '',
            'invoices_counter' => 0,
            'invoices_counter_year' => '',
        ]);

        $this->nextRound();

        $this->populateOrder($this->sample_order);

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->sample_order->id);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $order->aggregate, null);

        $this->nextRound();
        $receipts = \App\Receipt::all();
        $this->assertEquals($order->bookings->count(), $receipts->count());

        foreach ($receipts as $receipt) {
            $this->assertEquals(1, $receipt->bookings->count());
        }
    }
}
