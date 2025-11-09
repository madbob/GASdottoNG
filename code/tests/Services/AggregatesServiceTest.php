<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Aggregate;
use App\Supplier;
use App\Role;

class AggregatesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create()
    {
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        $supplier3 = Supplier::factory()->create();

        $role = Role::factory()->create(['actions' => 'supplier.orders']);
        $this->userAdmin->addRole($role->id, Supplier::class);
        $this->actingAs($this->userAdmin);

        $this->nextRound();

        $start1 = date('Y-m-d');
        $end1 = date('Y-m-d', strtotime('+20 days'));
        $shipping1 = date('Y-m-d', strtotime('+30 days'));

        $aggregate1 = app()->make('OrdersService')->store([
            'supplier' => $supplier1->id,
            'comment' => 'Commento di prova',
            'start' => printableDate($start1),
            'end' => printableDate($end1),
            'shipping' => printableDate($shipping1),
            'status' => 'open',
        ]);

        $this->assertEquals(1, $aggregate1->orders()->count());

        $this->nextRound();

        $start2 = date('Y-m-d', strtotime('+5 days'));
        $end2 = date('Y-m-d', strtotime('+30 days'));
        $shipping2 = date('Y-m-d', strtotime('+40 days'));

        $aggregate2 = app()->make('OrdersService')->store([
            'supplier' => $supplier2->id,
            'comment' => 'Commento di prova',
            'start' => printableDate($start2),
            'end' => printableDate($end2),
            'shipping' => printableDate($shipping2),
            'status' => 'open',
        ]);

        $this->assertEquals(1, $aggregate2->orders()->count());

        $this->nextRound();

        $orders = [];

        foreach($aggregate1->orders as $ord) {
            $orders[] = $ord->id;
        }

        foreach($aggregate2->orders as $ord) {
            $orders[] = $ord->id;
        }

        app()->make('AggregatesService')->store([
            'data' => json_encode([
                (object) [
                    'id' => 'new',
                    'orders' => $orders,
                ]
            ]),
        ]);

        $this->nextRound();

        $aggregates = Aggregate::all();
        $this->assertEquals(1, $aggregates->count());

        foreach($aggregates as $aggregate) {
            $this->assertEquals(2, $aggregate->orders->count());
            $this->assertEquals($start1, $aggregate->start->format('Y-m-d'));
            $this->assertEquals($end2, $aggregate->end->format('Y-m-d'));
            $this->assertEquals($shipping1, $aggregate->shipping->format('Y-m-d'));

            $aggregate->printableHeader();
            $aggregate->getBookingURL();
        }

        $empty = Aggregate::supplier($supplier3->id)->get();
        $this->assertEquals(0, $empty->count());

        $filled = Aggregate::supplier($supplier1->id)->get();
        $this->assertEquals(1, $filled->count());
    }

    public function test_create_large()
    {
        $suppliers = [];
        $orders = [];

        for($i = 0; $i < aggregatesConvenienceLimit() + 1; $i++) {
            $suppliers[] = Supplier::factory()->create();
        }

        $role = Role::factory()->create(['actions' => 'supplier.orders']);
        $this->userAdmin->addRole($role->id, Supplier::class);
        $this->actingAs($this->userAdmin);

        foreach($suppliers as $supplier) {
            $start = date('Y-m-d', strtotime('+' . rand(0, 5) . ' days'));
            $end = date('Y-m-d', strtotime('+' . rand(5, 10) . ' days'));
            $shipping = date('Y-m-d', strtotime('+' . rand(10, 20) . ' days'));

            $aggregate = app()->make('OrdersService')->store([
                'supplier' => $supplier->id,
                'comment' => 'Commento di prova',
                'start' => printableDate($start),
                'end' => printableDate($end),
                'shipping' => printableDate($shipping),
                'status' => 'open',
            ]);

            foreach($aggregate->orders as $ord) {
                $orders[] = $ord->id;
            }
        }

        app()->make('AggregatesService')->store([
            'data' => json_encode([
                (object) [
                    'id' => 'new',
                    'orders' => $orders,
                ]
            ]),
        ]);

        $this->nextRound();

        $aggregates = Aggregate::all();
        $this->assertEquals(1, $aggregates->count());

        foreach($aggregates as $aggregate) {
            $this->assertEquals(count($suppliers), $aggregate->orders->count());
            $aggregate->printableDates();
        }
    }
}
