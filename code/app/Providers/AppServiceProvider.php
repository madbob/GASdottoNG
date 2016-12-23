<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;
use Artisan;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Artisan::call('migrate');
    }

    public function register()
    {
    }
}
