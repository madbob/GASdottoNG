<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class EnforcePassword
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            if ($user->enforce_password_change) {
                $route_name = $request->route()->getName();
                if ($route_name != 'users.password' && $route_name != 'users.update') {
                    return redirect()->route('users.password');
                }
            }
        }

        return $next($request);
    }
}
