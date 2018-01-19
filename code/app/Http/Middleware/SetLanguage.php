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
        $user = Auth::user();
        if ($user != null)
            $gas = $user->gas;
        else
            $gas = Gas::first();

        $locale = $gas->getConfig('language');
        setlocale(LC_TIME, $locale. '.UTF-8');
        LaravelGettext::setLocale($locale);

        return $next($request);
    }
}
