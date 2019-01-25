<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\User;
use App\Order;
use App\Role;
use App\Delivery;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
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
            if ($order->status == 'open')
                $order->sendNotificationMail();
        });

        Order::updating(function($order) {
            if ($order->status == 'open') {
                $old = Order::find($order->id);
                if ($old->status != 'open')
                    $order->sendNotificationMail();
            }
        });
    }

    public function register()
    {
    }
}
