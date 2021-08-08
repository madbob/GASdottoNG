<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Observers\UserObserver;
use App\Observers\SupplierObserver;
use App\Observers\OrderObserver;

use App\User;
use App\Supplier;
use App\Order;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::observe(UserObserver::class);
        Supplier::observe(SupplierObserver::class);
        Order::observe(OrderObserver::class);
    }

    public function register()
    {
    }
}
