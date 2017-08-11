<?php

namespace Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected $baseUrl = 'http://localhost';

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
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

    public function tearDown()
    {
        $this->disableQueryDump();

        Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
