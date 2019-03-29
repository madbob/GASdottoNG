<?php

namespace App\Listeners;

use App\Events\AttachableToGas;

use Auth;
use Log;

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
        if (is_null($group)) {
            Log::error('Relazione oggetto/GAS non riconosciuta per oggetto ' . get_class($event->attachable));
            return;
        }

        $user->gas->$group()->attach($event->attachable->id);
    }
}
