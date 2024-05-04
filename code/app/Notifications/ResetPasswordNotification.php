<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class ResetPasswordNotification extends ManyMailNotification implements ShouldQueue
{
    use Queueable, MailFormatter;

    private $reset_token = null;

    public function __construct($token)
    {
        $this->reset_token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, $notifiable, 'password_reset', [
            'gas_reset_link' => route('password.reset', ['token' => $this->reset_token]),
        ]);
    }
}
