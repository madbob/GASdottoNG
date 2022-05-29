<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class VatRateServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->vat_rate = \App\VatRate::factory()->create();

        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Aliquota IVA con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['vat_rates']->store(array());
    }

    /*
        Salvataggio Aliquota IVA con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userAdmin);

        $newVat = $this->services['vat_rates']->store(array(
            'name' => '22%',
            'percentage' => 22,
        ));

        $this->assertEquals('22%', $newVat->name);
        $this->assertEquals(22, $newVat->percentage);
    }

    /*
        Modifica Aliquota IVA
    */
    public function testUpdate()
    {
        $this->actingAs($this->userAdmin);

        $vat = $this->services['vat_rates']->update($this->vat_rate->id, array(
            'name' => 'pippo',
            'percentage' => 22,
        ));

        $this->assertEquals('pippo', $vat->name);
        $this->assertEquals(22, $vat->percentage);
    }

    /*
        Cancellazione Aliquota IVA con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['vat_rates']->destroy($this->vat_rate->id);
    }

    /*
        Cancellazione Aliquota IVA
    */
    public function testDestroy()
    {
        $this->actingAs($this->userAdmin);
        $this->services['vat_rates']->destroy($this->vat_rate->id);

        $this->expectException(ModelNotFoundException::class);
        $this->services['vat_rates']->show($this->vat_rate->id);
    }

    /*
        TODO: Integrare con test sull'effettivo calcolo dell'IVA nel contesto di
        un ordine
    */
}
