<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

use App\Measure;
use App\Category;
use App\MovementType;

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

            /*
                Oltre che al login, faccio il controllo sull'eventuale modalità
                di manutenzione attiva anche qui per intercettare gli utenti
                precedentemente loggati (e magari col flag remember_me settato)
            */
            if ($user->gas->restricted == '1' && $user->can('gas.access', $user->gas) == false) {
                Auth::logout();
                return redirect(url('login'));
            }
        }

        return $next($request);
    }
}
