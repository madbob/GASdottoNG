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
        if (is_null($user))
            return;

        $group = $event->group;
        if (is_null($group))
            return;

        $user->gas->$group()->attach($event->attachable->id);
    }
}
