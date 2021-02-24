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
        $role = \App\Role::create([
            'name' => Str::random(10),
            'actions' => $permissions
        ]);

        $user = factory(\App\User::class)->create(['gas_id' => $gas->id]);
        $user->addRole($role, $target ?: $gas);

        return $user;
    }
}
