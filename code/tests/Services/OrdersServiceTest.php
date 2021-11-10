<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

use Artisan;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class OrdersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = \App\Gas::factory()->create();

        $this->sample_supplier = \App\Supplier::factory()->create();

        $this->category = \App\Category::factory()->create();
        $this->measure = \App\Measure::factory()->create();

        for($i = 0; $i < 10; $i++) {
            $this->products[] = \App\Product::factory()->create([
                'supplier_id' => $this->sample_supplier->id,
                'category_id' => $this->category->id,
                'measure_id' => $this->measure->id
            ]);
        }

        $this->sample_order = \App\Order::factory()->create([
            'supplier_id' => $this->sample_supplier->id,
        ]);

        $this->userAdmin = $this->createRoleAndUser($this->gas, 'gas.config');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.orders', $this->sample_supplier);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        Model::reguard();

        $this->service = new \App\Services\OrdersService();
    }

    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        $this->service->store(array(
            'supplier_id' => $this->sample_supplier->id,
        ));
    }

    public function testStore()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = $this->service->store(array(
            'supplier_id' => $this->sample_supplier->id,
            'comment' => 'Commento di prova',
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ));

        $this->assertEquals(1, $aggregate->orders->count());

        foreach($aggregate->orders as $order) {
            $this->assertEquals($this->sample_supplier->id, $order->supplier_id);
            $this->assertEquals('Commento di prova', $order->comment);
            $this->assertEquals($start, $order->start);
            $this->assertEquals($end, $order->end);
            $this->assertEquals($shipping, $order->shipping);
            $this->assertEquals(10, $order->products()->count());
            $this->assertEquals(0, $order->bookings()->count());
            $this->assertEquals($aggregate->id, $order->aggregate_id);
            $this->assertEquals('open', $order->status);
        }
    }

    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->service->update($this->sample_order->id, array());
    }

    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithReferrerPerms);
        $this->service->update('broken', array());
    }

    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $new_shipping = date('Y-m-d', strtotime('+40 days'));

        $this->service->update($this->sample_order->id, array(
            'comment' => 'Un altro commento',
            'shipping' => $new_shipping,
        ));

        $order = $this->service->show($this->sample_order->id);

        $this->assertEquals($order->comment, 'Un altro commento');
        $this->assertEquals($order->shipping, $new_shipping);
        $this->assertEquals($order->start, $this->sample_order->start);
        $this->assertEquals($order->end, $this->sample_order->end);
    }

    public function testOnShippingPlace()
    {
        $this->actingAs($this->userAdmin);
        $delivery = \App\Delivery::factory()->create([
            'default' => true,
        ]);

        $this->actingAs($this->userWithReferrerPerms);
        $this->service->update($this->sample_order->id, array(
            'deliveries' => [$delivery->id],
        ));

        $order = $this->service->show($this->sample_order->id);
        $this->assertEquals(1, $order->deliveries()->count());

        /*
            TODO: spostare funzione list() in OrdersService e testare che
            userWithNoPerms non veda l'ordine appena modificato

            $this->userWithNoPerms->preferred_delivery_id = $delivery->id;
            $this->actingAs($this->userWithNoPerms);
        */
    }

    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->service->show('random');
    }

    public function testShow()
    {
        $this->actingAs($this->userWithNoPerms);
        $order = $this->service->show($this->sample_order->id);

        $this->assertEquals($this->sample_order->id, $order->id);
        $this->assertEquals($this->sample_order->name, $order->name);
    }

    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->service->destroy($this->sample_order->id);
    }

    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->service->destroy($this->sample_order->id);
        $this->expectException(ModelNotFoundException::class);
        $order = $this->service->show($this->sample_order->id);
    }
}
