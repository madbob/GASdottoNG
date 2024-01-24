<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;

class AfterNotification
{
    public function handle(NotificationSent $event)
    {
        if (hasTrait($event->notification, 'App\Notifications\TemporaryFiles')) {
            // @phpstan-ignore-next-line
            $files = $event->notification->getFiles();
            foreach($files as $f) {
                @unlink($f);
            }
        }
    }
}
