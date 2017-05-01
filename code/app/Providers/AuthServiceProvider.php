<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $all_permissions = Role::allPermissions();
        foreach ($all_permissions as $class => $rules) {
            foreach ($rules as $identifier => $name) {
                $gate->define($identifier, function ($user, $obj = null) use ($identifier) {
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
