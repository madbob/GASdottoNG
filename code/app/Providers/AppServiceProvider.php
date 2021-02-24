<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Log;

use App\Observers\SupplierObserver;

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
            $default_roles = Role::where('always', true)->get();
            foreach($default_roles as $dr) {
                $user->addRole($dr, $user->gas);
            }

            if ($user->isFriend()) {
                $user->preferred_delivery_id = '';
            }
            else {
                $fallback_delivery = Delivery::where('default', true)->first();
                if ($fallback_delivery != null) {
                    $user->preferred_delivery_id = $fallback_delivery->id;
                }
            }

            $user->save();
        });

        Order::created(function($order) {
            if ($order->status == 'open') {
                async_job('order_open', ['order_id' => $order->id]);
            }
        });

        Order::updated(function($order) {
            if ($order->wasChanged('status')) {
                if ($order->status == 'open') {
                    async_job('order_open', ['order_id' => $order->id]);
                }
                else if ($order->status == 'closed') {
                    async_job('order_close', ['order_id' => $order->id]);
                }
            }
        });

        Supplier::observe(SupplierObserver::class);
    }

    public function register()
    {
    }
}
