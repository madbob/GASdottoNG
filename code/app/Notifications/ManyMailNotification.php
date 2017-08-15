<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ManyMailNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    protected function initMailMessage($notifiable)
    {
        $message = new MailMessage();

        if (class_uses(get_class($notifiable), 'App\ContactableTrait'))
            $notifiable->messageAll($message);

        return $message;
    }
}
