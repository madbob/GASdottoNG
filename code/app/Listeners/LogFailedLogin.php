<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event)
    {
        \Log::debug('Fallito login utente ' . $event->credentials['username']);
    }
}
