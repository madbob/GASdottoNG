<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class VatRateServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vat_rate = \App\VatRate::factory()->create();

        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Aliquota IVA con permessi sbagliati
    */
    public function test_fails_to_store()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('VatRatesService')->store([]);
    }

    /*
        Salvataggio Aliquota IVA con permessi corretti
    */
    public function test_store()
    {
        $this->actingAs($this->userAdmin);

        $newVat = app()->make('VatRatesService')->store([
            'name' => '22%',
            'percentage' => 22,
        ]);

        $this->assertEquals('22%', $newVat->name);
        $this->assertEquals(22, $newVat->percentage);
    }

    /*
        Modifica Aliquota IVA
    */
    public function test_update()
    {
        $this->actingAs($this->userAdmin);

        $vat = app()->make('VatRatesService')->update($this->vat_rate->id, [
            'name' => 'pippo',
            'percentage' => 22,
        ]);

        $this->assertEquals('pippo', $vat->name);
        $this->assertEquals(22, $vat->percentage);
    }

    /*
        Cancellazione Aliquota IVA con permessi sbagliati
    */
    public function test_fails_to_destroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('VatRatesService')->destroy($this->vat_rate->id);
    }

    /*
        Cancellazione Aliquota IVA
    */
    public function test_destroy()
    {
        $this->actingAs($this->userAdmin);
        app()->make('VatRatesService')->destroy($this->vat_rate->id);

        $this->expectException(ModelNotFoundException::class);
        app()->make('VatRatesService')->show($this->vat_rate->id);
    }

    /*
        TODO: Integrare con test sull'effettivo calcolo dell'IVA nel contesto di
        un ordine
    */
}
