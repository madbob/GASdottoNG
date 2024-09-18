<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Extensions\BypassUserProvider;

use Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        app()->make('RolesService')->registerPolicies();

        Auth::provider('bypass', function ($app, array $config) {
            return new BypassUserProvider($app['hash'], $config['model']);
        });
    }
}
