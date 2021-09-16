<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordNotification extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    private $reset_token = null;

    public function __construct($token)
    {
        $this->reset_token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, 'password_reset', [
            'username' => $notifiable->username,
            'gas_reset_link' => route('password.reset', ['token' => $this->reset_token]),
        ]);
    }
}
