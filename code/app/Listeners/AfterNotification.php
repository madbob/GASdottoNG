<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;

class AfterNotification
{
    public function __construct()
    {
        //
    }

    public function handle(NotificationSent $event)
    {
        if (hasTrait($event->notification, 'App\Notifications\TemporaryFiles')) {
            $files = $event->notification->getFiles();
            foreach($files as $f) {
                @unlink($f);
            }
        }
    }
}
