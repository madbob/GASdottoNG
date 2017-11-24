<?php

namespace App\Notifications;

use Auth;

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
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable);
        $message->subject('Nuova notifica dal GAS')->replyTo($user)->view('emails.notification', ['notification' => $this->notification]);
        return $message;
    }
}
