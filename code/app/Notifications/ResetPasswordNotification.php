<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;

class ResetPasswordNotification extends ManyMailNotification
{
    private $reset_token = null;

    public function __construct($token)
    {
        $this->reset_token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->view('emails.resetpassword', ['url' => url('password/reset/' . $this->reset_token)]);
        return $message;
    }
}
