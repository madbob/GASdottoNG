<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use LaravelGettext;

use App\Gas;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = currentLang();
        setlocale(LC_TIME, $locale. '.UTF-8');
        LaravelGettext::setLocale($locale);
        return $next($request);
    }
}
