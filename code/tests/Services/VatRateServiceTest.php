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

        $this->gas = \App\Gas::factory()->create();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'gas.config');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        $this->vatsService = new \App\Services\VatRatesService();
    }

    /*
        Salvataggio Aliquota IVA con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->vatsService->store(array());
    }

    /*
        Salvataggio Aliquota IVA con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $newVat = $this->vatsService->store(array(
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
