<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Singletons\OrderNumbersDispatcher;

class SingletonsProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('OrderNumbersDispatcher', function ($app) {
            return new OrderNumbersDispatcher();
        });
    }
}
