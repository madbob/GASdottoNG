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

        $this->services['suppliers']->store(array(
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

        $supplier = $this->services['suppliers']->store(array(
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ));

        $this->assertEquals('Test Supplier', $supplier->name);
        $this->assertEquals('Test Supplier SRL', $supplier->business_name);
        $this->assertEquals(0, $supplier->currentBalanceAmount());
    }

    /*
        Salvataggio Fornitore con lo stesso nome
    */
    public function testStoreSameName()
    {
        $this->actingAs($this->userWithAdminPerm);

        $supplier = $this->services['suppliers']->store(array(
            'name' => 'Test',
        ));

        $other_supplier = $this->services['suppliers']->store(array(
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

        $suppliers = $this->services['suppliers']->list();
        $this->assertCount(0, $suppliers);
    }

    /*
        Elenco Fornitori corretto
    */
    public function testList()
    {
        $this->actingAs($this->userWithNormalPerms);

        $suppliers = $this->services['suppliers']->list();
        $this->assertCount(1, $suppliers);
    }

    /*
        Modifica Fornitore con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['suppliers']->update($this->supplier->id, array());
    }

    /*
        Modifica Fornitore con permessi sbagliati
    */
    public function testFailsToUpdateByAdmin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['suppliers']->update($this->supplier->id, array());
    }

    /*
        Modifica Fornitore con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNormalPerms);
        $this->services['suppliers']->update('id', array());
    }

    /*
        Modifica Fornitore
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $supplier = $this->services['suppliers']->update($this->supplier->id, array(
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
        $this->services['suppliers']->show('random');
    }

    /*
        Accesso Fornitore
    */
    public function testShow()
    {
        $this->actingAs($this->userWithNormalPerms);

        $supplier = $this->services['suppliers']->show($this->supplier->id);

        $this->assertEquals($this->supplier->id, $supplier->id);
        $this->assertEquals($this->supplier->name, $supplier->name);
        $this->assertEquals($this->supplier->business_name, $supplier->business_name);
    }

    /*
        Cancellazione Fornitore con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['suppliers']->destroy($this->supplier->id);
    }

    /*
        Cancellazione Fornitore
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);
        $supplier = $this->services['suppliers']->destroy($this->supplier->id);
        $this->assertNotNull($supplier->deleted_at);

        $this->actingAs($this->userWithAdminPerm);
        $supplier = $this->services['suppliers']->destroy($this->supplier->id);

        try {
            $this->services['suppliers']->show($this->supplier->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            // good boy
        }
    }
}
