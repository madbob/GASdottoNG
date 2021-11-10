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

    public function createRoleAndUser($gas, $permissions, $target = null)
    {
        $role = \App\Role::factory()->create([
            'actions' => $permissions
        ]);

        $user = \App\User::factory()->create(['gas_id' => $gas->id]);
        $user->addRole($role->id, $target ?: $gas);

        return $user;
    }
}
