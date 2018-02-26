<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Singletons\OrderNumbersDispatcher;
use App\Singletons\MovementsHub;

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

        $this->app->singleton('MovementsHub', function ($app) {
            return new MovementsHub();
        });
    }
}
