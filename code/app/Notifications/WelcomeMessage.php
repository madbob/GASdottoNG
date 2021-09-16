<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeMessage extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, 'welcome', [
            'username' => $notifiable->username,
            'gas_login_link' => route('login')
        ]);
    }
}
