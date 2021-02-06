<?php

namespace App\Http\Middleware;

use Closure;

use App\Measure;
use App\Category;
use App\MovementType;

/*
    Questo middleware è destinato ad ospitare eventuali correzioni "in corsa" al
    database, per creare o ricreare elementi che per default dovrebbero sempre
    esserci
*/
class FixDatabase
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
