<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;

class VatRateServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

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
        TODO: Integrare con test sull'effettivo calcolo dell'IVA nel contesto di
        un ordine
    */
}
