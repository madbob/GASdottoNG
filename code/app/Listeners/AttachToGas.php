<?php

namespace App\Listeners;

use App\Events\AttachableToGas;

use Auth;
use Log;

class AttachToGas
{
    public function handle(AttachableToGas $event)
    {
        $user = Auth::user();
        if (is_null($user)) {
            $gas = $event->attachable->guessGas();
        }
        else {
            $gas = [$user->gas];
        }

        $group = $event->group;
        if (is_null($group)) {
            Log::error('Relazione oggetto/GAS non riconosciuta per oggetto ' . get_class($event->attachable));
            return;
        }

        foreach($gas as $g) {
            $g->$group()->attach($event->attachable->id);
        }
    }
}
