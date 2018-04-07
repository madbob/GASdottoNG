<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;

class NewUserNotification extends ManyMailNotification
{
    private $user = null;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(_i('Nuovo utente registrato'))->view('emails.newuser', ['user' => $this->user]);
        return $message;
    }
}
