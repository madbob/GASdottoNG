<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;

use Closure;
use Session;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = currentLang();
        setlocale(LC_TIME, $locale. '.UTF-8');
        App::setLocale($locale);
        return $next($request);
    }
}
