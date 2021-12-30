<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class OrdersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = $this->initOrder(null);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Creazione Ordine con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        $this->services['orders']->store(array(
            'supplier_id' => $this->order->supplier_id,
        ));
    }

    /*
        Creazione Ordine
    */
    public function testStore()
    {
        $this->actingAs($this->userReferrer);

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = $this->services['orders']->store(array(
            'supplier_id' => $this->order->supplier_id,
            'comment' => 'Commento di prova',
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ));

        $this->assertEquals(1, $aggregate->orders->count());

        foreach($aggregate->orders as $order) {
            $this->assertEquals($this->order->supplier_id, $order->supplier_id);
            $this->assertEquals('Commento di prova', $order->comment);
            $this->assertEquals($start, $order->start);
            $this->assertEquals($end, $order->end);
            $this->assertEquals($shipping, $order->shipping);
            $this->assertEquals($this->order->supplier->products()->count(), $order->products()->count());
            $this->assertEquals(0, $order->bookings()->count());
            $this->assertEquals($aggregate->id, $order->aggregate_id);
            $this->assertEquals('open', $order->status);
        }
    }

    /*
        Modifica Ordine con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['orders']->update($this->order->id, array());
    }

    /*
        Modifica Ordine con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userReferrer);
        $this->services['orders']->update('broken', array());
    }

    /*
        Modifica Ordine
    */
    public function testUpdate()
    {
        $this->actingAs($this->userReferrer);

        $new_shipping = date('Y-m-d', strtotime('+40 days'));

        $this->services['orders']->update($this->order->id, array(
            'comment' => 'Un altro commento',
            'shipping' => $new_shipping,
        ));

        $order = $this->services['orders']->show($this->order->id);

        $this->assertEquals($order->comment, 'Un altro commento');
        $this->assertEquals($order->shipping, $new_shipping);
        $this->assertEquals($order->start, $this->order->start);
        $this->assertEquals($order->end, $this->order->end);
    }

    /*
        Assegnazione Luoghi di Consegna
    */
    public function testOnShippingPlace()
    {
        $this->actingAs($this->userAdmin);
        $delivery = \App\Delivery::factory()->create([
            'default' => true,
        ]);

        $this->actingAs($this->userReferrer);
        $this->services['orders']->update($this->order->id, array(
            'deliveries' => [$delivery->id],
        ));

        $order = $this->services['orders']->show($this->order->id);
        $this->assertEquals(1, $order->deliveries()->count());

        /*
            TODO: spostare funzione list() in OrdersService e testare che
            userWithNoPerms non veda l'ordine appena modificato

            $this->userWithNoPerms->preferred_delivery_id = $delivery->id;
            $this->actingAs($this->userWithNoPerms);
        */
    }

    /*
        Accesso Ordine con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['orders']->show('random');
    }

    /*
        Accesso Ordine
    */
    public function testShow()
    {
        $this->actingAs($this->userWithNoPerms);
        $order = $this->services['orders']->show($this->order->id);

        $this->assertEquals($this->order->id, $order->id);
        $this->assertEquals($this->order->name, $order->name);
    }

    /*
        Cancellazione Ordine con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['orders']->destroy($this->order->id);
    }

    /*
        Cancellazione Ordine
    */
    public function testDestroy()
    {
        $this->actingAs($this->userReferrer);

        $this->services['orders']->destroy($this->order->id);
        $this->expectException(ModelNotFoundException::class);
        $order = $this->services['orders']->show($this->order->id);
    }

    /*
        Assegnazione numeri agli ordini
    */
    public function testNumbers()
    {
        $this->actingAs($this->userReferrer);

        $this_year = date('Y');

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = $this->services['orders']->store(array(
            'supplier_id' => $this->order->supplier_id,
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ));

        $order = $this->services['orders']->show($this->order->id);
        $this->assertEquals($order->internal_number, '1 / ' . $this_year);

        foreach($aggregate->orders as $order) {
            $order = $this->services['orders']->show($order->id);
            $this->assertEquals($order->internal_number, '2 / ' . $this_year);
        }

        $second_aggregate = $this->services['orders']->store(array(
            'supplier_id' => $this->order->supplier_id,
            'start' => printableDate(date('Y-m-d', strtotime($start . ' +1 year'))),
            'end' => printableDate(date('Y-m-d', strtotime($end . ' +1 year'))),
            'status' => 'closed',
        ));

        $order = $this->services['orders']->show($this->order->id);
        $this->assertEquals($order->internal_number, '1 / ' . $this_year);

        foreach($aggregate->orders as $order) {
            $order = $this->services['orders']->show($order->id);
            $this->assertEquals($order->internal_number, '2 / ' . $this_year);
        }

        foreach($second_aggregate->orders as $order) {
            $order = $this->services['orders']->show($order->id);
            $this->assertEquals($order->internal_number, '1 / ' . ($this_year + 1));
        }
    }

    /*
        Modificatori ereditati dal fornitore
    */
    public function testInitModifiers()
    {
        $this->actingAs($this->userReferrer);

        $this->order->supplier->applicableModificationTypes();
        $mod = $this->order->supplier->modifiers()->where('modifier_type_id', 'spese-trasporto')->first();
        $this->assertNotNull($mod);
        $this->services['modifiers']->update($mod->id, [
            'value' => 'absolute',
            'arithmetic' => 'sum',
            'scale' => 'minor',
            'applies_type' => 'none',
            'applies_target' => 'order',
            'distribution_type' => 'price',
            'simplified_amount' => 30,
        ]);

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = $this->services['orders']->store(array(
            'supplier_id' => $this->order->supplier_id,
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ));

        $this->assertEquals(1, $aggregate->orders->count());

        foreach($aggregate->orders as $order) {
            $order = $this->services['orders']->show($order->id);
            $this->assertEquals($order->modifiers->count(), 1);
            $this->assertEquals($order->modifiers->first()->modifierType->id, 'spese-trasporto');
        }
    }
}
