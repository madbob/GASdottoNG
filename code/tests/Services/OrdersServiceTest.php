<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;
use Bus;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

use App\User;
use App\Delivery;
use App\Booking;
use App\VariantCombo;

class OrdersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = $this->initOrder(null);
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
		$this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');
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
        Bus::fake();

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

        Bus::assertDispatched(\App\Jobs\NotifyNewOrder::class);
        $this->assertEquals(1, $aggregate->orders->count());
        $this->assertTrue($aggregate->isActive());
        $this->assertTrue($aggregate->isRunning());
        $this->assertFalse($aggregate->canShip());

        $this->actingAs($this->userWithShippingPerms);
        $this->assertTrue($aggregate->canShip());

        $this->actingAs($this->userReferrer);

        foreach($aggregate->orders as $order) {
            $order = $this->services['orders']->show($order->id);

            $this->assertEquals($this->order->supplier_id, $order->supplier_id);
            $this->assertEquals('Commento di prova', $order->comment);
            $this->assertEquals($start, $order->start);
            $this->assertEquals($end, $order->end);
            $this->assertEquals($shipping, $order->shipping);
            $this->assertEquals($this->order->supplier->products()->count(), $order->products()->count());
            $this->assertEquals(0, $order->bookings()->count());
            $this->assertEquals($aggregate->id, $order->aggregate_id);
            $this->assertEquals('open', $order->status);
            $this->assertNotNull($order->supplier);
            $this->assertNotNull($order->printableName());
            $this->assertNotNull($order->statusIcons());
            $this->assertNotNull($order->printableDates());
            $this->assertNotNull($order->printableHeader());
            $this->assertTrue($order->isActive());
            $this->assertTrue($order->isRunning());
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
        Cambio stato
    */
    public function testChangeState()
    {
        Bus::fake();

        $this->actingAs($this->userReferrer);

        $this->services['orders']->update($this->order->id, array(
            'status' => 'closed',
        ));

        $this->nextRound();
        $order = $this->services['orders']->show($this->order->id);
        $this->assertTrue($order->isActive());
        $this->assertFalse($order->isRunning());

        $this->services['orders']->update($this->order->id, array(
            'status' => 'open',
        ));

        Bus::assertDispatched(\App\Jobs\NotifyNewOrder::class);

        $this->nextRound();
        $order = $this->services['orders']->show($this->order->id);
        $this->assertTrue($order->isActive());
        $this->assertTrue($order->isRunning());

        $this->services['orders']->update($this->order->id, array(
            'status' => 'shipped',
        ));

        $this->nextRound();
        $order = $this->services['orders']->show($this->order->id);
        $this->assertFalse($order->isActive());
        $this->assertFalse($order->isRunning());
    }

    /*
        Assegnazione Luoghi di Consegna
    */
    public function testOnShippingPlace()
    {
        $this->actingAs($this->userAdmin);
        $delivery = Delivery::factory()->create([
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

		$this->nextRound();

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

    /*
        Esportazione GDXP
    */
    public function testExportGDXP()
    {
        $this->actingAs($this->userReferrer);
        $this->assertNotNull($this->order->exportXML());
        $this->assertNotNull($this->order->exportJSON());
    }

	/*
		Modifica prodotti nell'ordine
	*/
	public function testRemoveProduct()
	{
		$this->actingAs($this->userWithBasePerms);

		$target_product_1 = $this->order->products()->orderBy('id', 'asc')->first();
		$target_product_2 = $this->order->products()->orderBy('id', 'asc')->skip(1)->first();

		$data = [
			'action' => 'booked',
			$target_product_1->id => 2,
			$target_product_2->id => 3,
		];

		$booking = $this->updateAndFetch($data, $this->order, $this->userWithBasePerms, false);

		$this->nextRound();
		$booking = Booking::find($booking->id);
		$this->assertEquals($booking->products()->count(), 2);
		$this->assertEquals($this->order->bookings()->count(), 1);

		$this->actingAs($this->userReferrer);

		$this->services['orders']->update($this->order->id, [
            'supplier_id' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
			'enabled' => $this->order->products->filter(function($p) use ($target_product_1) {
				return $p->id != $target_product_1->id;
			})->pluck('id')->toArray(),
        ]);

		$this->nextRound();
		$booking = Booking::find($booking->id);
		$this->assertEquals($booking->products()->count(), 1);
		$this->assertEquals($this->order->bookings()->count(), 1);

		$this->services['orders']->update($this->order->id, [
            'supplier_id' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
			'enabled' => $this->order->products->filter(function($p) use ($target_product_1, $target_product_2) {
				return $p->id != $target_product_1->id && $p->id != $target_product_2->id;
			})->pluck('id')->toArray(),
        ]);

		$this->nextRound();
		$this->assertEquals($this->order->bookings()->count(), 0);
		$booking = Booking::find($booking->id);
		$this->assertNull($booking);
	}

    /*
        Cambio prezzo di un prodotto
    */
    public function testChangeProductPrice()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->products()->inRandomOrder()->first();
        $old_price = $product->getPrice();
        $new_price = $old_price + 2;
        $this->services['products']->update($product->id, array(
            'name' => $product->name,
            'price' => $new_price,
        ));

        $this->nextRound();

        $product_order = $this->order->products()->where('product_id', $product->id)->first();
        $this->assertEquals($old_price, $product_order->getPrice());

        $product_raw = $this->services['products']->show($product->id);
        $this->assertEquals($new_price, $product_raw->getPrice());

        $this->assertFalse($product_raw->comparePrices($product_order));
    }

    /*
        Cambio prezzo di una variante
    */
    public function testChangeProductVariantPrice()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->supplier->products()->inRandomOrder()->first();
        $product_price = $product->getPrice();

        $ids = [];
        $active = [];
        $variant = $this->createVariant($product);
        foreach($variant->values as $index => $val) {
            $ids[] = $val->id;

            $combo = VariantCombo::byValues([$val->id]);
            $actives[] = $combo->id;
        }

        $this->services['variants']->matrix($product, $ids, $actives, ['', '', ''], [0, 0, 0], [0, 0, 0]);

        $this->nextRound();

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

        $combos = $aggregate->orders->first()->products()->where('product_id', $product->id)->first()->variantCombos;
        $this->assertFalse($combos->isEmpty());
        foreach($combos as $combo) {
            $this->assertEquals($product_price, $combo->getPrice());
        }

        $this->nextRound();

        $product = $this->services['products']->show($product->id);
        $this->services['variants']->matrix($product, $ids, $actives, ['', '', ''], [0, 0, 1], [0, 0, 0]);

        $this->nextRound();

        $product = $this->services['products']->show($product->id);
        $new_product = $aggregate->orders->first()->products()->where('product_id', $product->id)->first();

        $combos = $new_product->variantCombos;
        $this->assertFalse($combos->isEmpty());
        $this->assertEquals(3, $combos->count());

        foreach($combos as $index => $combo) {
            $listing_combo = VariantCombo::find($combo->id);
            if ($index == 2) {
                $this->assertEquals($listing_combo->getPrice(), $combo->getPrice() + 1);
            }
            else {
                $this->assertEquals($listing_combo->getPrice(), $combo->getPrice());
            }
        }

        $this->assertFalse($product->comparePrices($new_product));
    }
}
