<?php

namespace App\Jobs;

use Log;

use App\Notifications\GenericNotificationWrapper;

use App\Notification;

class DeliverNotification extends Job
{
    public $notification_id;

    public function __construct($notification_id)
    {
        parent::__construct();
        $this->notification_id = $notification_id;
    }

    protected function realHandle()
    {
        $notification = Notification::findOrFail($this->notification_id);

        foreach ($notification->users as $user) {
            try {
                $user->notify(new GenericNotificationWrapper($notification));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inoltrare mail di notifica a utente ' . $user->id . ': ' . $e->getMessage());
            }
        }
    }
}
