<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class FastBookingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->sample_order = $this->initOrder(null);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');
    }

    /*
        Consegne veloci
    */
    public function testFastShipping()
    {
        $this->populateOrder($this->sample_order);

        $this->actingAs($this->userWithShippingPerms);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, null);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);

        foreach($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'shipped');
            $this->assertNotNull($booking->payment);

            foreach($booking->products as $product) {
                $this->assertEquals($product->quantity, $product->delivered);
            }

            $this->assertEquals($booking->payment->amount, $booking->getValue('effective', true));
        }
    }

    /*
        Consegne veloci parziali
    */
    public function testFastShippingFiltered()
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

        foreach($order->bookings as $booking) {
            if (in_array($booking->user_id, $users)) {
                $this->assertEquals($booking->status, 'shipped');
                $this->assertNotNull($booking->payment);

                foreach($booking->products as $product) {
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
        Consegne veloci su prenotazioni salvate
    */
    public function testFastShippingPreSaved()
    {
        $this->populateOrder($this->sample_order);

		$this->nextRound();
		$this->actingAs($this->userWithShippingPerms);
		$order = app()->make('OrdersService')->show($this->sample_order->id);

		$new_data = [];

		foreach($order->bookings as $booking) {
            list($data, $booked_count, $total) = $this->randomQuantities($order->products);
            $data['action'] = 'saved';
            app()->make('BookingsService')->bookingUpdate($data, $order->aggregate, $booking->user, true);
			$new_data[$booking->user->id] = $data;
        }

		$this->nextRound();
		$order = app()->make('OrdersService')->show($this->sample_order->id);
		foreach($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'saved');
		}

		$this->nextRound();
        $this->actingAs($this->userWithShippingPerms);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, null);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->sample_order->id);

        foreach($order->bookings as $booking) {
            $this->assertEquals($booking->status, 'shipped');
            $this->assertNotNull($booking->payment);
			$data = $new_data[$booking->user->id];

            foreach($booking->products as $product) {
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
    public function testFastShippingReceipts()
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

        foreach($receipts as $receipt) {
            $this->assertEquals(1, $receipt->bookings->count());
        }
    }
}
