<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;

class GenericNotificationWrapper extends ManyMailNotification
{
    private $notification = null;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject('Nuova notifica dal GAS')->view('emails.notification', ['notification' => $this->notification]);
        return $message;
    }
}
