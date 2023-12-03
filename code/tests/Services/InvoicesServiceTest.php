<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;
use App\User;
use App\Supplier;
use App\Order;
use App\Movement;

class InvoicesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory()->create();
        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'movements.admin');
        $this->userWithSupplierPerm = $this->createRoleAndUser($this->gas, 'supplier.movements', $this->supplier);
        $this->userWithInvoicesPerm = $this->createRoleAndUser($this->gas, 'supplier.invoices', $this->supplier);
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Fattura con permessi sbagliati (utente)
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $today = date('Y-m-d');

        app()->make('InvoicesService')->store([
            'number' => 'ABC123',
            'supplier_id' => $this->supplier->id,
            'date' => printableDate($today),
        ]);
    }

    /*
        Salvataggio Fattura con permessi sbagliati (amministratore)
    */
    public function testFailsToStoreAdmin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);

        app()->make('InvoicesService')->store([
            'number' => 'ABC123',
            'supplier_id' => $this->supplier->id,
            'date' => printableDate(date('Y-m-d')),
        ]);
    }

    /*
        Salvataggio Fattura con permessi sbagliati (fornitore sbagliato)
    */
    public function testFailsToStoreSupplier()
    {
        $this->expectException(AuthException::class);
        $other_supplier = Supplier::factory()->create();
        $this->actingAs($this->userWithInvoicesPerm);

        $invoice = app()->make('InvoicesService')->store(array(
            'number' => 'ABC123',
            'supplier_id' => $other_supplier->id,
            'date' => printableDate(date('Y-m-d')),
        ));

        return app()->make('InvoicesService')->show($invoice->id);
    }

    private function createInvoice()
    {
        $this->actingAs($this->userWithInvoicesPerm);

        $invoice = app()->make('InvoicesService')->store(array(
            'number' => 'ABC123',
            'supplier_id' => $this->supplier->id,
            'date' => printableDate(date('Y-m-d')),
        ));

        return app()->make('InvoicesService')->show($invoice->id);
    }

	private function wireOrders($invoice)
	{
		$order1 = Order::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

		$order2 = Order::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        $this->actingAs($this->userWithInvoicesPerm);
		app()->make('InvoicesService')->wire($invoice->id, 'review', [
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

		$invoice = app()->make('InvoicesService')->show($invoice->id);
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
        app()->make('InvoicesService')->list($past, $future, '0');
    }

    /*
        Elenco Fatture corretto
    */
    public function testList()
    {
        $this->createInvoice();
        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));

        foreach([$this->userWithInvoicesPerm, $this->userWithSupplierPerm, $this->userWithAdminPerm] as $index => $user) {
            $this->actingAs($user);

            $this->nextRound();
            $user = app()->make('UsersService')->show($user->id);

            $invoices = app()->make('InvoicesService')->list($past, $future, '0');
            $this->assertEquals(1, $invoices->count());

            $this->nextRound();

            $invoices = app()->make('InvoicesService')->list($past, $future, $this->supplier->id);
            $this->assertEquals(1, $invoices->count());
        }
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

        $this->actingAs($this->userWithInvoicesPerm);

		app()->make('InvoicesService')->wire($invoice->id, 'review', [
			'order_id' => [$order1->id, $order2->id]
		]);

        $this->nextRound();

        $products = app()->make('InvoicesService')->products($invoice->id);
        $this->assertEquals($order1->products->count() + $order2->products->count(), count($products['global_summary']->products));
	}

    /*
        Pagamento fattura
    */
    public function testPayAndList()
    {
        $invoice = $this->createInvoice();

        $this->nextRound();

        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));
        $invoices = app()->make('InvoicesService')->list($past, $future, '0');
        $this->assertEquals(1, $invoices->count());

        $this->nextRound();

        $future = date('Y-m-d', strtotime('-3 days'));
        $invoices = app()->make('InvoicesService')->list($past, $future, '0');
        $this->assertEquals(0, $invoices->count());

        $this->nextRound();

		$this->wireOrders($invoice);

		$this->nextRound();

        $this->actingAs($this->userWithSupplierPerm);

        app()->make('InvoicesService')->saveMovements($invoice->id, [
            'type' => ['invoice-payment'],
            'amount' => [10],
            'method' => ['bank'],
            'notes' => [''],
        ]);

        $this->nextRound();

        $movements = Movement::where('type', 'invoice-payment')->get();
        $this->assertEquals(1, $movements->count());
        $movement = $movements->first();
        $this->assertEquals(10, $movement->amount);

		$invoice = app()->make('InvoicesService')->show($invoice->id);
        $this->assertEquals($invoice->payment->id, $movement->id);
        $this->assertEquals($invoice->status, 'payed');

		foreach($invoice->orders as $order) {
			$this->assertEquals($movement->id, $order->payment_id);
		}
    }

    /*
        Pagamento fattura con permessi sbagliati
    */
    public function testWrongPayment()
    {
        $this->expectException(AuthException::class);

        $invoice = $this->createInvoice();
        $this->nextRound();
		$this->wireOrders($invoice);
		$this->nextRound();

        $this->actingAs($this->userWithInvoicesPerm);

        app()->make('InvoicesService')->saveMovements($invoice->id, [
            'type' => ['invoice-payment'],
            'amount' => [10],
            'method' => ['bank'],
            'notes' => [''],
        ]);
    }

    /*
        Modifica Fattura con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $invoice = $this->createInvoice();

        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('InvoicesService')->update($invoice->id, array());
    }

    /*
        Modifica Fattura con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithSupplierPerm);
        app()->make('InvoicesService')->update('id', array());
    }

    /*
        Modifica Fattura
    */
    public function testUpdate()
    {
        $invoice = $this->createInvoice();
        $old_date = date('Y-m-d', strtotime('-10 days'));

        app()->make('InvoicesService')->update($invoice->id, array(
            'number' => '123ABC',
            'date' => $old_date,
        ));

        $invoice = app()->make('InvoicesService')->show($invoice->id);
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
        app()->make('InvoicesService')->show('random');
    }

    /*
        Cancellazione Fattura con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $invoice = $this->createInvoice();
        $this->nextRound();

        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('InvoicesService')->destroy($invoice->id);
    }

    /*
        Cancellazione Fattura
    */
    public function testDestroy()
    {
        $invoice = $this->createInvoice();
		$this->wireOrders($invoice);
        $invoice = app()->make('InvoicesService')->show($invoice->id);
        $this->assertNotNull($invoice);

		$orders = $invoice->orders;
		$this->assertEquals(2, $orders->count());

		$this->nextRound();

        $movement = Movement::generate('invoice-payment', $this->userWithSupplierPerm->gas, $invoice, 10);
        $movement->save();

		$this->nextRound();

		foreach($orders as $order) {
			$order = app()->make('OrdersService')->show($order->id);
			$this->assertEquals($movement->id, $order->payment_id);
		}

		$this->nextRound();

        $invoice = app()->make('InvoicesService')->destroy($invoice->id);

        try {
            app()->make('InvoicesService')->show($invoice->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }

		$this->nextRound();

		foreach($orders as $order) {
			$order = app()->make('OrdersService')->show($order->id);
			$this->assertNull($order->payment_id);
		}
    }
}
