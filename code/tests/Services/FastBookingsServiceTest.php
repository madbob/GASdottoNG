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
        Test consegne veloci
    */
    public function testFastShipping()
    {
        $this->populateOrder($this->sample_order);

        $this->actingAs($this->userWithShippingPerms);
        $this->services['fast_bookings']->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, null);

        $this->nextRound();
        $order = $this->services['orders']->show($this->sample_order->id);

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
        Test consegne veloci parziali
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
        $this->services['fast_bookings']->fastShipping($this->userWithShippingPerms, $this->sample_order->aggregate, $filter);

        $this->nextRound();
        $order = $this->services['orders']->show($this->sample_order->id);

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
}
