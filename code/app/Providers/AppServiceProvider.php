<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Artisan;

use App\User;
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

            $fallback_delivery = Delivery::where('default', true)->first();
            if ($fallback_delivery != null) {
                $user->preferred_delivery_id = $fallback_delivery->id;
                $user->save();
            }
        });
    }

    public function register()
    {
    }
}
