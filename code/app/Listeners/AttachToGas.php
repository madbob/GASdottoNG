<?php

namespace App\Listeners;

use App\Events\AttachableToGas;

use Auth;

class AttachToGas
{
    public function __construct()
    {
        //
    }

    public function handle(AttachableToGas $event)
    {
        $user = Auth::user();
        if ($user == null)
            return;

        $group = $event->group;
        if ($group == null)
            return;

        $user->gas->$group()->attach($event->attachable->id);
    }
}
