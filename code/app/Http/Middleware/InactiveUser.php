<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class InactiveUser
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->pending) {
            return redirect()->route('users.blocked');
        }

        return $next($request);
    }
}
