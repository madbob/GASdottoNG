<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class InvoicesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = \App\Supplier::factory()->create();
        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'movements.admin');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Fattura con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $this->services['invoices']->store(array(
            'number' => 'ABC123',
            'supplier_id' => $this->supplier->id,
        ));
    }

    private function createInvoice()
    {
        $this->actingAs($this->userWithAdminPerm);
        $today = date('Y-m-d');

        $invoice = $this->services['invoices']->store(array(
            'number' => 'ABC123',
            'supplier_id' => $this->supplier->id,
            'date' => printableDate($today),
        ));

        return $this->services['invoices']->show($invoice->id);
    }

	private function wireOrders($invoice)
	{
		$order1 = \App\Order::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

		$order2 = \App\Order::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

		$this->services['invoices']->wire($invoice->id, 'review', [
			'order_id' => [$order1->id, $order2->id]
		]);
	}

    /*
        Salvataggio Fattura
    */
    public function testStore()
    {
        $invoice = $this->createInvoice();

        $this->assertEquals('ABC123', $invoice->number);
        $this->assertEquals($this->supplier->id, $invoice->supplier->id);
        $this->assertEquals(date('Y-m-d'), $invoice->date);
        $this->assertEquals(0, $invoice->orders()->count());
        $this->assertEquals(0, $invoice->otherMovements()->count());
        $this->assertNull($invoice->payment);

		$this->wireOrders($invoice);

		$this->nextRound();

		$invoice = $this->services['invoices']->show($invoice->id);
		$this->assertEquals(2, $invoice->orders()->count());

        return $invoice;
    }

    /*
        Permessi sbagliati su elenco Fatture
    */
    public function testNoList()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));
        $this->services['invoices']->list($past, $future, 0);
    }

    /*
        Elenco Fatture corretto
    */
    public function testList()
    {
        $this->createInvoice();

        $this->nextRound();

        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));
        $invoices = $this->services['invoices']->list($past, $future, '0');
        $this->assertEquals(1, $invoices->count());

        $this->nextRound();

        $invoices = $this->services['invoices']->list($past, $future, '42');
        $this->assertEquals(0, $invoices->count());

        $this->nextRound();

        $invoices = $this->services['invoices']->list($past, $future, $this->supplier->id);
        $this->assertEquals(1, $invoices->count());
    }

    /*
        Contenuto della Invoice
    */
    public function testContents()
	{
		$invoice = $this->createInvoice();

        $order1 = $this->initOrder(null);
        $this->populateOrder($order1);

        $order2 = $this->initOrder(null);
        $this->populateOrder($order2);

        $this->nextRound();

        $this->actingAs($this->userWithAdminPerm);

		$this->services['invoices']->wire($invoice->id, 'review', [
			'order_id' => [$order1->id, $order2->id]
		]);

        $this->nextRound();

        $products = $this->services['invoices']->products($invoice->id);
        $this->assertEquals($order1->products->count() + $order2->products->count(), count($products['global_summary']->products));
	}

    /*
        Elenco Fatture pagate
    */
    public function testPayAndList()
    {
        $invoice = $this->createInvoice();

        $this->nextRound();

        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));
        $invoices = $this->services['invoices']->list($past, $future, '0');
        $this->assertEquals(1, $invoices->count());

        $this->nextRound();

        $future = date('Y-m-d', strtotime('-3 days'));
        $invoices = $this->services['invoices']->list($past, $future, '0');
        $this->assertEquals(1, $invoices->count());

        $this->nextRound();

		$this->wireOrders($invoice);

		$this->nextRound();

        $movement = \App\Movement::generate('invoice-payment', $this->userWithAdminPerm->gas, $invoice, 10);
        $movement->save();

        $invoices = $this->services['invoices']->list($past, $future, '0');
        $this->assertEquals(0, $invoices->count());

		$this->nextRound();

		$invoice = $this->services['invoices']->show($invoice->id);
		foreach($invoice->orders as $order) {
			$this->assertEquals($movement->id, $order->payment_id);
		}
    }

    /*
        Modifica Fattura con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $invoice = $this->createInvoice();

        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['invoices']->update($invoice->id, array());
    }

    /*
        Modifica Fattura con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['invoices']->update('id', array());
    }

    /*
        Modifica Fattura
    */
    public function testUpdate()
    {
        $invoice = $this->createInvoice();
        $old_date = date('Y-m-d', strtotime('-10 days'));

        $this->services['invoices']->update($invoice->id, array(
            'number' => '123ABC',
            'date' => $old_date,
        ));

        $invoice = $this->services['invoices']->show($invoice->id);
        $this->assertEquals($old_date, $invoice->date);
        $this->assertEquals('123ABC', $invoice->number);
    }

    /*
        Accesso Fattura con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['invoices']->show('random');
    }

    /*
        Cancellazione Fattura con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['invoices']->destroy('random');
    }

    /*
        Cancellazione Fattura
    */
    public function testDestroy()
    {
        $invoice = $this->createInvoice();
		$this->wireOrders($invoice);
        $invoice = $this->services['invoices']->show($invoice->id);
        $this->assertNotNull($invoice);

		$orders = $invoice->orders;
		$this->assertEquals(2, $orders->count());

		$this->nextRound();

        $movement = \App\Movement::generate('invoice-payment', $this->userWithAdminPerm->gas, $invoice, 10);
        $movement->save();

		$this->nextRound();

		foreach($orders as $order) {
			$order = $this->services['orders']->show($order->id);
			$this->assertEquals($movement->id, $order->payment_id);
		}

		$this->nextRound();

        $invoice = $this->services['invoices']->destroy($invoice->id);

        try {
            $this->services['invoices']->show($invoice->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }

		$this->nextRound();

		foreach($orders as $order) {
			$order = $this->services['orders']->show($order->id);
			$this->assertNull($order->payment_id);
		}
    }
}
