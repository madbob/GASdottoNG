<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class ManualWelcomeMessage extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    private $token = null;

    public function __construct($token)
    {
        $this->afterCommit = true;
        $this->token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, $notifiable, 'manual_welcome', [
            'gas_access_link' => route('autologin', ['token' => $this->token]),
        ]);
    }
}
