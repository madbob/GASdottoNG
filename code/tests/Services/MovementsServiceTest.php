<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class MovementsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'movements.admin');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'movements.view');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        $this->sample_movement = \App\Movement::factory()->create([
            'type' => 'donation-from-gas',
            'method' => 'bank',
            'sender_id' => $this->gas->id,
            'sender_type' => 'App\Gas',
            'registerer_id' => $this->userWithAdminPerm->id,
            'currency_id' => defaultCurrency()->id,
        ]);
    }

    /*
        Accesso Movimenti
    */
    public function testList()
    {
        $this->actingAs($this->userWithAdminPerm);
        $this->services['movements']->list([]);

        $this->actingAs($this->userWithReferrerPerms);
        $this->services['movements']->list([]);

        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['movements']->list([]);
    }

    /*
        Creazione Movimento
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);
        $currency = defaultCurrency();

        $this->services['movements']->store(array(
            'type' => 'donation-from-gas',
            'method' => 'bank',
            'sender_id' => $this->gas->id,
            'sender_type' => 'App\Gas',
            'currency_id' => $currency->id,
            'amount' => 100
        ));

        $amount = 100 + $this->sample_movement->amount;

        $this->assertEquals($amount * -1, $this->gas->currentBalanceAmount($currency));
    }

    /*
        Ricalcolo saldi
    */
    public function testRecalculate()
    {
        $this->testStore();

        $currency = defaultCurrency();

        $this->gas->alterBalance(50, $currency);
        $amount = 100 - 50 + $this->sample_movement->amount;
        $this->assertEquals($amount * -1, $this->gas->currentBalanceAmount($currency));

        $this->services['movements']->recalculate();

        $amount = 100 + $this->sample_movement->amount;
        $this->assertEquals($amount * -1, $this->gas->currentBalanceAmount($currency));
    }

    /*
        Archivio saldi
    */
    public function testCloseBalance()
    {
        $this->testStore();

        $previous_balances = \App\Balance::all()->count();

        $this->services['movements']->closeBalance([
            'date' => printableDate(date('Y-m-d')),
        ]);

        $now_balances = \App\Balance::all()->count();
        $this->assertEquals($previous_balances * 2, $now_balances);

        $now_balances = \App\Balance::where('current', true)->count();
        $this->assertEquals($previous_balances, $now_balances);
    }

    /*
        Modifica Movimento con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithReferrerPerms);
        $this->services['movements']->update($this->sample_movement->id, []);
    }

    /*
        Modifica Movimento con ID non esistente
    */
    public function testFailsToUpdateBecauseNoMovementWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['movements']->update('id', array());
    }

    /*
        Modifica Movimento
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->services['movements']->update($this->sample_movement->id, [
            'amount' => 50
        ]);

        $this->nextRound();

        $this->gas = $this->gas->fresh();
        $this->assertEquals(-50, $this->gas->currentBalanceAmount());
    }

    /*
        Accesso Movimento con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithReferrerPerms);

        $this->services['movements']->show('random');
    }

    /*
        Accesso Movimento
    */
    public function testShow()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $movement = $this->services['movements']->show($this->sample_movement->id);

        $this->assertEquals($this->sample_movement->id, $movement->id);
        $this->assertEquals($this->sample_movement->amount, $movement->amount);
    }

    /*
        Cancellazione Movimento con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $this->services['movements']->destroy($this->sample_movement->id);
    }

    /*
        Cancellazione Movimento
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);
        $this->services['movements']->destroy($this->sample_movement->id);
        $this->assertEquals(0, $this->gas->currentBalanceAmount());

        try {
            $this->services['movements']->show($this->sample_movement->id);
            $this->fail('should never run');
        } catch (ModelNotFoundException $e) {
            //good boy
        }
    }

    /*
        Versamento, assegnazione e scadenza quote di iscrizione
    */
    public function testUserFees()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->userWithNoPerms->gas->setConfig('annual_fee_amount', 5);

        $this->services['movements']->store(array(
            'type' => 'annual-fee',
            'method' => 'bank',
            'target_id' => $this->userWithNoPerms->gas->id,
            'target_type' => 'App\Gas',
            'sender_id' => $this->userWithNoPerms->id,
            'sender_type' => 'App\User',
            'amount' => $this->userWithNoPerms->gas->getConfig('annual_fee_amount'),
        ));

        $reloaded = \App\User::find($this->userWithNoPerms->id);
        $this->assertNotEquals($reloaded->fee_id, 0);
        $fee = \App\Movement::find($reloaded->fee_id);
        $this->assertEquals($fee->amount, 5);

        $expiration = date('Y-m-d', strtotime('-5 days'));
        $this->userWithNoPerms->gas->setConfig('year_closing', $expiration);
        Artisan::call('check:fees');

        $this->userWithNoPerms->fresh();
        $this->assertEquals($this->userWithNoPerms->fee_id, 0);

        $new_date = $this->userWithNoPerms->gas->getConfig('year_closing');
        $expiration = date('Y-m-d', strtotime($expiration . ' +1 years'));
        $this->assertEquals($new_date, $expiration);
    }

    /*
        Salvataggio e modifica movimento con IntegralCES attivo
    */
    public function testWithIntegralces()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->userWithAdminPerm->gas->setConfig('integralces', (object) [
            'enabled' => true,
            'identifier' => '12345',
            'symbol' => 'ø',
        ]);

        $currency_default = defaultCurrency();
        $currency_integralces = \App\Currency::where('context', 'integralces')->first();

        $this->services['movements']->store(array(
            'type' => 'donation-from-gas',
            'method' => 'bank',
            'sender_id' => $this->gas->id,
            'sender_type' => 'App\Gas',
            'currency_id' => $currency_integralces->id,
            'amount' => 100
        ));

        $this->assertEquals($this->sample_movement->amount * -1, $this->gas->currentBalanceAmount($currency_default));
        $this->assertEquals(-100, $this->gas->currentBalanceAmount($currency_integralces));

        $integralces_movement = \App\Movement::where('currency_id', $currency_integralces->id)->first();
        $this->services['movements']->update($integralces_movement->id, array(
            'currency_id' => $currency_default->id,
        ));

        $this->assertEquals(($this->sample_movement->amount + 100) * -1, $this->gas->currentBalanceAmount($currency_default));
        $this->assertEquals(0, $this->gas->currentBalanceAmount($currency_integralces));
    }
}
