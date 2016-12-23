<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PermissionsCache;

class PermissionsCacheProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton('PermissionsCache', function ($app) {
            return new PermissionsCache();
        });
    }
}
