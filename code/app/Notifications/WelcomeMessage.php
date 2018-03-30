<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;

class WelcomeMessage extends ManyMailNotification
{
    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(_i('Benvenuto!'))->view('emails.welcome', ['user' => $notifiable]);
        return $message;
    }
}
