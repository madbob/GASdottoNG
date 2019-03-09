<?php

namespace App\Listeners;

use Illuminate\Log\Events\MessageLogged;

use App;

class HarvestLogs
{
    public function __construct()
    {
        //
    }

    public function handle(MessageLogged $event)
    {
        $harvester = App::make('LogHarvester');
        $harvester->push($event->message);
    }
}
