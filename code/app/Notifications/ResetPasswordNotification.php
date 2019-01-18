<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;
use App\Notifications\MailFormatter;

class ResetPasswordNotification extends ManyMailNotification
{
    use MailFormatter;

    private $reset_token = null;

    public function __construct($token)
    {
        $this->reset_token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, 'password_reset', [
            'gas_reset_link' => url('password/reset/' . $this->reset_token)
        ]);
    }
}
