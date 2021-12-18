<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class MaintainanceMode
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->gas->restricted == '1' && $user->can('gas.access', $user->gas) == false) {
            Auth::logout();
            return redirect()->route('login');
        }

        return $next($request);
    }
}
