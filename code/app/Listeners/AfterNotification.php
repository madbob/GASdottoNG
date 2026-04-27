<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;

use App\Notifications\Concerns\TemporaryFiles;

class AfterNotification
{
    public function handle(NotificationSent $event)
    {
        if (hasTrait($event->notification, TemporaryFiles::class)) {
            $files = $event->notification->getFiles();
            foreach ($files as $f) {
                @unlink($f);
            }
        }
    }
}
