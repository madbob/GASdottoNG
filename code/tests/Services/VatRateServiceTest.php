<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Model;

class VatRateServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Model::unguard();

        $this->gas = factory(\App\Gas::class)->create();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'gas.config');
        $this->userWithNoPerms = factory(\App\User::class)->create(['gas_id' => $this->gas->id]);

        Model::reguard();

        $this->vatsService = new \App\Services\VatRatesService();
    }

    /**
     * @expectedException \App\Exceptions\AuthException
     */
    public function testFailsToStore()
    {
        $this->actingAs($this->userWithNoPerms);

        $this->vatsService->store(array());
    }

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
