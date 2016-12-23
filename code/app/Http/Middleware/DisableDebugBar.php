<?php

namespace App\Http\Middleware;

use Closure;

class DisableDebugBar
{
    public function handle($request, Closure $next)
    {
        app('debugbar')->disable();

        return $next($request);
    }
}
