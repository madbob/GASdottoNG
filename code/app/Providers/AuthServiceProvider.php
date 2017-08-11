<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $all_permissions = Role::allPermissions();
        foreach ($all_permissions as $class => $rules) {
            foreach ($rules as $identifier => $name) {
                Gate::define($identifier, function ($user, $obj = null) use ($identifier) {
                    foreach($user->roles as $role) {
                        if ($role->enabledAction($identifier)) {
                            if($obj == null || $role->applies($obj)) {
                                return true;
                            }
                        }
                    }

                    return false;
                });
            }
        }
    }
}
