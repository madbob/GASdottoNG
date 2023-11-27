<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class SuppliersServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = \App\Supplier::factory()->create();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNormalPerms = $this->createRoleAndUser($this->gas, 'supplier.view');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Fornitore con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        app()->make('SuppliersService')->store(array(
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ));
    }

    /*
        Salvataggio Fornitore
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $supplier = app()->make('SuppliersService')->store([
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ]);

        $this->assertEquals('Test Supplier', $supplier->name);
        $this->assertEquals('Test Supplier SRL', $supplier->business_name);
        $this->assertEquals(0, $supplier->currentBalanceAmount());

        /*
            Verifica generazione degli allegati autogenerati (listini)
        */

        $attachments = $supplier->attachments;
        $this->assertEquals(2, $attachments->count());

        $has_pdf = false;
        $has_csv = false;

        foreach($attachments as $attach) {
            $this->assertEquals(1, $attach->internal);

            if (str_ends_with($attach->url, 'pdf')) {
                $has_pdf = true;
            }
            else if (str_ends_with($attach->url, 'csv')) {
                $has_csv = true;
            }
        }

        $this->assertTrue($has_pdf);
        $this->assertTrue($has_csv);
    }

    /*
        Salvataggio Fornitore con lo stesso nome
    */
    public function testStoreSameName()
    {
        $this->actingAs($this->userWithAdminPerm);

        $supplier = app()->make('SuppliersService')->store(array(
            'name' => 'Test',
        ));

        $other_supplier = app()->make('SuppliersService')->store(array(
            'name' => 'Test',
        ));

        $this->assertNotEquals($supplier->id, $other_supplier->id);
    }

    /*
        Permessi sbagliati su elenco Fornitori
    */
    public function testNoList()
    {
        $this->actingAs($this->userWithNoPerms);

        $suppliers = app()->make('SuppliersService')->list();
        $this->assertCount(0, $suppliers);
    }

    /*
        Elenco Fornitori corretto
    */
    public function testList()
    {
        $this->actingAs($this->userWithNormalPerms);

        $suppliers = app()->make('SuppliersService')->list();
        $this->assertCount(1, $suppliers);
    }

    /*
        Modifica Fornitore con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('SuppliersService')->update($this->supplier->id, array());
    }

    /*
        Modifica Fornitore con permessi sbagliati
    */
    public function testFailsToUpdateByAdmin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('SuppliersService')->update($this->supplier->id, array());
    }

    /*
        Modifica Fornitore con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNormalPerms);
        app()->make('SuppliersService')->update('id', array());
    }

    /*
        Modifica Fornitore
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $supplier = app()->make('SuppliersService')->update($this->supplier->id, array(
            'taxcode' => '12345',
            'vat' => '09876',
        ));

        $this->assertNotEquals($supplier->taxcode, $this->supplier->taxcode);
        $this->assertEquals(0, $supplier->currentBalanceAmount());
    }

    /*
        Accesso Fornitore con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNormalPerms);
        app()->make('SuppliersService')->show('random');
    }

    /*
        Accesso Fornitore
    */
    public function testShow()
    {
        $this->actingAs($this->userWithNormalPerms);

        $supplier = app()->make('SuppliersService')->show($this->supplier->id);

        $this->assertEquals($this->supplier->id, $supplier->id);
        $this->assertEquals($this->supplier->name, $supplier->name);
        $this->assertEquals($this->supplier->business_name, $supplier->business_name);
    }

    /*
        Funzioni varie di integrazione tra ordine e fornitore
    */
    public function testOrderRelations()
    {
        $order = $this->initOrder(null, $this->supplier);
        $this->populateOrder($order);

        $order->supplier->addContact('email', 'foobar@example.com');
        $this->userWithReferrerPerms->addContact('email', 'barbaz@example.com');

        $this->nextRound();

        $this->actingAs($this->userWithReferrerPerms);

        $supplier = app()->make('SuppliersService')->show($order->supplier_id);
        $order = app()->make('OrdersService')->show($order->id);

        $this->assertEquals(1, $supplier->active_orders->count());
        $this->assertEquals(2, $supplier->involvedEmails()->count());

        $this->assertTrue($order->bookings->count() > 0);
        $this->assertEquals($order->bookings->count(), $supplier->bookings->count());
    }

    /*
        Esportazione Fornitore
    */
    public function testExport()
    {
        $order = $this->initOrder(null);
        $this->assertNotNull($order->supplier->exportJSON());
    }

    /*
        Cancellazione Fornitore con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('SuppliersService')->destroy($this->supplier->id);
    }

    /*
        Cancellazione Fornitore
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);
        $supplier = app()->make('SuppliersService')->destroy($this->supplier->id);
        $this->assertNotNull($supplier->deleted_at);

        $this->actingAs($this->userWithAdminPerm);
        $supplier = app()->make('SuppliersService')->destroy($this->supplier->id);

        try {
            app()->make('SuppliersService')->show($this->supplier->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }
    }
}
