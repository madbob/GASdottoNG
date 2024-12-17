<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Notifications\GenericNotificationWrapper;
use App\Notification;

class DeliverNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notification_id;

    public function __construct($notification_id)
    {
        $this->notification_id = $notification_id;
    }

    public function handle()
    {
        $notification = Notification::findOrFail($this->notification_id);

        foreach ($notification->users as $user) {
            try {
                $user->notify(new GenericNotificationWrapper($notification));
            }
            catch (\Exception $e) {
                \Log::error('Impossibile inoltrare mail di notifica a utente ' . $user->id . ': ' . $e->getMessage());
            }
        }
    }
}
