<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Observers\MovementObserver;
use App\Observers\UserObserver;
use App\Observers\SupplierObserver;
use App\Observers\OrderObserver;
use App\Observers\ModifierObserver;
use App\Observers\ContactObserver;
use App\Observers\VariantObserver;
use App\Observers\ConfigObserver;

use App\Movement;
use App\User;
use App\Supplier;
use App\Order;
use App\Modifier;
use App\Contact;
use App\Variant;
use App\Config;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);

        Movement::observe(MovementObserver::class);
        User::observe(UserObserver::class);
        Supplier::observe(SupplierObserver::class);
        Order::observe(OrderObserver::class);
        Modifier::observe(ModifierObserver::class);
        Contact::observe(ContactObserver::class);
        Variant::observe(VariantObserver::class);
        Config::observe(ConfigObserver::class);
    }

    public function register()
    {
    }
}
