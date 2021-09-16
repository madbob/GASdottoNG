<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManualWelcomeMessage extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    public $afterCommit = true;
    private $token = null;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, 'manual_welcome', [
            'username' => $notifiable->username,
            'gas_access_link' => route('autologin', ['token' => $this->token]),
            'gas_login_link' => route('login'),
        ]);
    }
}
