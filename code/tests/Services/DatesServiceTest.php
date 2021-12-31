<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;
use Artisan;

class DatesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier1 = \App\Supplier::factory()->create();
        $this->supplier2 = \App\Supplier::factory()->create();
        $this->supplier3 = \App\Supplier::factory()->create();

        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        $role = \App\Role::factory()->create([
            'actions' => 'supplier.orders,notifications.admin'
        ]);

        $this->userAdmin->addRole($role->id, $this->supplier1);
        $this->userAdmin->addRole($role->id, $this->supplier2);
        $this->userAdmin->addRole($role->id, $this->gas);

        $this->userReferrer = \App\User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userReferrer->addRole($role->id, $this->supplier3);
    }

    /*
        Salvataggio Date con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['dates']->update(0, array());
    }

    /*
        Salvataggio e rimozione Date con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userAdmin);

        $this->services['dates']->update(0, [
            'id' => ['', ''],
            'target_id' => [$this->supplier1->id, $this->supplier2->id],
            'date' => [date('Y-m-d', strtotime('+5 days')), ''],
            'recurring' => ['', 'Lunedì - Ogni due Settimane - ' . printableDate(date('Y-m-d')) . ' - ' . printableDate(date('Y-m-d', strtotime('+6 months')))],
            'description' => ['Test 1', 'Test 2'],
            'type' => ['confirmed', 'temp'],
        ]);

        $dates = \App\Date::all();
        $this->assertEquals($dates->count(), 2);

        $this->nextRound();

        $this->actingAs($this->userReferrer);

        $this->services['dates']->update(0, [
            'id' => [''],
            'target_id' => [$this->supplier3->id],
            'date' => [date('Y-m-d', strtotime('+5 days'))],
            'recurring' => [''],
            'description' => ['Test 3'],
            'type' => ['confirmed'],
        ]);

        $dates = \App\Date::all();
        $this->assertEquals($dates->count(), 3);

        $this->nextRound();

        $this->actingAs($this->userAdmin);

        $this->services['dates']->update(0, [
            'id' => [],
            'target_id' => [],
            'date' => [],
            'recurring' => [],
            'description' => [],
            'type' => [],
        ]);

        $dates = \App\Date::all();
        $this->assertEquals($dates->count(), 1);
    }

    /*
        Salvataggio e apertura ordini ricorrenti
    */
    public function testOrders()
    {
        $this->actingAs($this->userAdmin);

        $days = localeDays();
        $weekday = null;
        $today = mb_strtolower(date('l'));
        foreach($days as $it => $en) {
            if ($en == $today) {
                $weekday = ucwords($it);
                break;
            }
        }

        $orders = \App\Order::all();
        $this->assertEquals($orders->count(), 0);

        $this->services['dates']->updateOrders([
            'id' => [''],
            'target_id' => [$this->supplier1->id],
            'recurring' => [$weekday . ' - Ogni due Settimane - ' . printableDate(date('Y-m-d')) . ' - ' . printableDate(date('Y-m-d', strtotime('+6 months')))],
            'end' => [10],
            'shipping' => [11],
            'comment' => [''],
            'suspend' => [],
        ]);

        $dates = \App\Date::all();
        $this->assertEquals($dates->count(), 1);

        $orders = \App\Order::where('status', 'open')->get();
        $this->assertEquals($orders->count(), 1);
        foreach($orders as $o) {
            $this->assertEquals($o->supplier_id, $this->supplier1->id);
            $this->assertEquals($o->start, date('Y-m-d'));
            $this->assertEquals($o->end, date('Y-m-d', strtotime('+10 days')));
            $this->assertEquals($o->shipping, date('Y-m-d', strtotime('+11 days')));
        }
    }
}
