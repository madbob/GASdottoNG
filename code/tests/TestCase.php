<?php

use Illuminate\Support\Facades\Artisan;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        putenv('DB_CONNECTION=sqlite_testing');

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

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
