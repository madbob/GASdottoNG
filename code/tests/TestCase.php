<?php

namespace Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
}
