<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Observers\UserObserver;
use App\Observers\SupplierObserver;
use App\Observers\OrderObserver;
use App\Observers\ModifierObserver;
use App\Observers\ContactObserver;

use App\User;
use App\Supplier;
use App\Order;
use App\Modifier;
use App\Contact;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::observe(UserObserver::class);
        Supplier::observe(SupplierObserver::class);
        Order::observe(OrderObserver::class);
        Modifier::observe(ModifierObserver::class);
        Contact::observe(ContactObserver::class);
    }

    public function register()
    {
    }
}
