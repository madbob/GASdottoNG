<?php

namespace Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

use Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $baseUrl = 'http://localhost';

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CurrenciesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'MovementTypesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'ModifierTypesSeeder']);

        /*
            Questo serve a generare le stringhe delle date in italiano, per la
            corretta formattazione da parte di printableDate()
        */
        setlocale(LC_TIME, 'it_IT.UTF-8');
    }

    public function enabledQueryDump()
    {
        \DB::listen(function ($sql, $bindings) {
            var_dump($sql);
            var_dump($bindings);
        });
    }

    public function disableQueryDump()
    {
        \DB::getEventDispatcher()->forget('illuminate.query');
    }

    public function tearDown(): void
    {
        $this->disableQueryDump();
        parent::tearDown();
    }

    /*
        Per creare un ruolo coi dati permessi ed assegnargli un utente
    */
    protected function createRoleAndUser($gas, $permissions, $target = null)
    {
        $role = \App\Role::factory()->create([
            'actions' => $permissions
        ]);

        $user = \App\User::factory()->create(['gas_id' => $gas->id]);
        $user->addRole($role->id, $target ?: $gas);

        return $user;
    }

    /*
        Per predisporre il minimo essenziale per fare delle prenotazioni.
        Ovvero: un ordine
    */
    protected function initOrder($other_order)
    {
        $category = \App\Category::factory()->create();
        $measure = \App\Measure::factory()->create();

        $supplier = \App\Supplier::factory()->create();

        for($i = 0; $i < 10; $i++) {
            $products[] = \App\Product::factory()->create([
                'supplier_id' => $supplier->id,
                'category_id' => $category->id,
                'measure_id' => $measure->id
            ]);
        }

        $order = \App\Order::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        if ($other_order != null) {
            $order->aggregate_id = $other_order->aggregate_id;
            $order->save();
        }

        return [$supplier, $products, $order];
    }

    protected function randomQuantities($products)
    {
        $data = [];
        $booked_count = 0;
        $total = 0;

        foreach($products as $product) {
            $q = rand(0, 5);

            $data[$product->id] = $q;

            if ($q != 0) {
                $booked_count++;
                $total += $product->price * $q;
            }
        }

        return [$data, $booked_count, $total];
    }
}
