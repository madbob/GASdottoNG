<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

use Carbon\Carbon;

use App\Exceptions\AuthException;
use App\Role;
use App\User;
use App\Supplier;
use App\Date;
use App\Order;

class DatesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier1 = Supplier::factory()->create();
        $this->supplier2 = Supplier::factory()->create();
        $this->supplier3 = Supplier::factory()->create();

        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);

        $role = Role::factory()->create([
            'actions' => 'supplier.orders,notifications.admin'
        ]);

        $this->userAdmin->addRole($role->id, $this->supplier1);
        $this->userAdmin->addRole($role->id, $this->supplier2);
        $this->userAdmin->addRole($role->id, $this->gas);

        $this->userReferrer = User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userReferrer->addRole($role->id, $this->supplier3);
    }

    /*
        Salvataggio Date con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('DatesService')->update(0, array());
    }

    /*
        Salvataggio e rimozione Date con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userAdmin);

        app()->make('DatesService')->update(0, [
            'id' => ['', ''],
            'target_id' => [$this->supplier1->id, $this->supplier2->id],
            'date' => [date('Y-m-d', strtotime('+5 days')), ''],
            'recurring' => ['', 'Lunedì - Ogni due Settimane - ' . printableDate(date('Y-m-d')) . ' - ' . printableDate(date('Y-m-d', strtotime('+6 months')))],
            'description' => ['Test 1', 'Test 2'],
            'type' => ['confirmed', 'temp'],
        ]);

        $dates = Date::all();
        $this->assertEquals($dates->count(), 2);

        $this->nextRound();

        $this->actingAs($this->userReferrer);

        app()->make('DatesService')->update(0, [
            'id' => [''],
            'target_id' => [$this->supplier3->id],
            'date' => [date('Y-m-d', strtotime('+5 days'))],
            'recurring' => [''],
            'description' => ['Test 3'],
            'type' => ['confirmed'],
        ]);

        $dates = Date::all();
        $this->assertEquals($dates->count(), 3);

        $this->nextRound();

        $this->actingAs($this->userAdmin);

        app()->make('DatesService')->update(0, [
            'id' => [],
            'target_id' => [],
            'date' => [],
            'recurring' => [],
            'description' => [],
            'type' => [],
        ]);

        $dates = Date::all();
        $this->assertEquals($dates->count(), 1);
    }

    private function getCurrentWeekday()
    {
        $weekday = null;

        $days = localeDays();
        $today = mb_strtolower(date('l'));
        foreach($days as $it => $en) {
            if ($en == $today) {
                $weekday = ucwords($it);
                break;
            }
        }

        return $weekday;
    }

    /*
        Salvataggio e apertura ordini ricorrenti
    */
    public function testOrders()
    {
        $this->actingAs($this->userAdmin);
        $weekday = $this->getCurrentWeekday();

        $orders = Order::all();
        $this->assertEquals($orders->count(), 0);

        app()->make('DatesService')->updateOrders([
            'id' => [''],
            'target_id' => [$this->supplier1->id],
            'recurring' => [$weekday . ' - Ogni due Settimane - ' . printableDate(date('Y-m-d')) . ' - ' . printableDate(date('Y-m-d', strtotime('+6 months')))],
            'action' => ['open'],
            'first_offset' => [10],
            'second_offset' => [11],
            'comment' => [''],
            'suspend' => [],
        ]);

        $dates = Date::all();
        $this->assertEquals($dates->count(), 1);

        $orders = Order::where('status', 'open')->get();
        $this->assertEquals($orders->count(), 1);
        foreach($orders as $o) {
            $this->assertEquals($o->supplier_id, $this->supplier1->id);
            $this->assertEquals($o->start, date('Y-m-d'));
            $this->assertEquals($o->end, date('Y-m-d', strtotime('+10 days')));
            $this->assertEquals($o->shipping, date('Y-m-d', strtotime('+11 days')));
        }

        Artisan::call('open:orders');
        $orders = Order::where('status', 'open')->get();
        $this->assertEquals($orders->count(), 1);
    }

    public function testRecurrences()
    {
        $this->actingAs($this->userAdmin);
        $recurrence = 'Lunedì - Secondo del Mese - ' . printableDate(date('Y-m-d')) . ' - ' . printableDate(date('Y-m-d', strtotime('+6 months')));

        /*
            Reminder: per le ricorrenze di chiusura e consegna si raccomanda si
            usare offset inferiori ai 7 giorni, in modo da non tornare indietro
            oltre una settimana.
            Così si minimizza - ma non si elimina del tutto - il rischio di far
            coincidere la data di apertura con quella odierna, che farebbe
            scattare l'apertura di un ordine automatico, che modificherebbe le
            prossime date di riferimento, che renderebbe invalidi i test
            successivi
        */
        app()->make('DatesService')->updateOrders([
            'id' => ['', '', ''],
            'target_id' => [$this->supplier1->id, $this->supplier1->id, $this->supplier1->id],
            'recurring' => [$recurrence, $recurrence, $recurrence],
            'action' => ['open', 'close', 'ship'],
            'first_offset' => [10, 6, 5],
            'second_offset' => [11, 3, 2],
            'comment' => ['', 'pippo', ''],
            'suspend' => [],
        ]);

        $this->nextRound();

        $dates = Date::all();
        $this->assertEquals($dates->count(), 3);
        $reference_monday = Carbon::parse('second monday of next month');
        $reference_monday_formatted = $reference_monday->format('Y-m-d');

        foreach($dates as $index => $date) {
            $this->assertEquals($this->supplier1->id, $date->target->id);
            $orders = $date->order_dates;
            $this->assertTrue(count($orders) > 0);
            $found = false;

            switch($index) {
                case 0:
                    $this->assertEquals('open', $date->action);
                    $this->assertEquals(10, $date->first_offset);
                    $this->assertEquals(11, $date->second_offset);
                    $this->assertEquals('', $date->comment);

                    foreach($orders as $order) {
                        if ($order->start == $reference_monday_formatted) {
                            $this->assertEquals($reference_monday->copy()->addDays(10)->format('Y-m-d'), $order->end);
                            $this->assertEquals($reference_monday->copy()->addDays(11)->format('Y-m-d'), $order->shipping);
                            $found = true;
                            break;
                        }
                    }

                    $this->assertTrue($found);
                    break;

                case 1:
                    $this->assertEquals('close', $date->action);
                    $this->assertEquals(6, $date->first_offset);
                    $this->assertEquals(3, $date->second_offset);
                    $this->assertEquals('pippo', $date->comment);

                    foreach($orders as $order) {
                        if ($order->end == $reference_monday_formatted) {
                            $this->assertEquals($reference_monday->copy()->subDays(6)->format('Y-m-d'), $order->start);
                            $this->assertEquals($reference_monday->copy()->addDays(3)->format('Y-m-d'), $order->shipping);
                            $found = true;
                            break;
                        }
                    }

                    $this->assertTrue($found);
                    break;

                case 2:
                    $this->assertEquals('ship', $date->action);
                    $this->assertEquals(5, $date->first_offset);
                    $this->assertEquals(2, $date->second_offset);
                    $this->assertEquals('', $date->comment);

                    foreach($orders as $order) {
                        if ($order->shipping == $reference_monday_formatted) {
                            $this->assertEquals($reference_monday->copy()->subDays(5)->format('Y-m-d'), $order->start);
                            $this->assertEquals($reference_monday->copy()->subDays(2)->format('Y-m-d'), $order->end);
                            $found = true;
                            break;
                        }
                    }

                    $this->assertTrue($found);
                    break;
            }

            $this->assertTrue($found);
        }
    }
}
