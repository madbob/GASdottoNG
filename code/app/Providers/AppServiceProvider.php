<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Artisan;

use App\User;
use App\Role;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Artisan::call('migrate');

        User::created(function($user) {
            $default_roles = Role::where('always', true)->get();
            foreach($default_roles as $dr) {
                $user->addRole($dr, $user->gas);
            }
        });
    }

    public function register()
    {
    }
}
