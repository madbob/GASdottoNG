<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Log;

use App\Observers\SupplierObserver;
use App\Jobs\NotifyNewOrder;
use App\Jobs\NotifyClosedOrder;
use App\User;
use App\Supplier;
use App\Order;
use App\Role;
use App\Delivery;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::created(function($user) {
            if ($user->isFriend()) {
                $default_role = $user->gas->roles['friend'] ?? -1;
                $role = Role::find($default_role);
                if (is_null($role)) {
                    $default_role = $user->gas->roles['user'];
                    $role = Role::find($default_role);
                }

                if ($role) {
                    $user->addRole($role, $user->gas);
                }

                $user->preferred_delivery_id = '';
            }
            else {
                $default_role = $user->gas->roles['user'];
                $role = Role::find($default_role);
                if ($role) {
                    $user->addRole($role, $user->gas);
                }

                $fallback_delivery = Delivery::where('default', true)->first();
                if ($fallback_delivery != null) {
                    $user->preferred_delivery_id = $fallback_delivery->id;
                }
            }

            $user->save();
        });

        Order::created(function($order) {
            if ($order->status == 'open') {
                NotifyNewOrder::dispatch($order->id);
            }
        });

        Order::updated(function($order) {
            if ($order->wasChanged('status')) {
                if ($order->status == 'open') {
                    NotifyNewOrder::dispatch($order->id);
                }
                else if ($order->status == 'closed') {
                    NotifyClosedOrder::dispatch($order->id);
                }
            }
        });

        Supplier::observe(SupplierObserver::class);
    }

    public function register()
    {
    }
}
