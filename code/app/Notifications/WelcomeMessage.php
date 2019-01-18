<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;
use App\Notifications\MailFormatter;

class WelcomeMessage extends ManyMailNotification
{
    use MailFormatter;

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, 'welcome', [
            'username' => $notifiable->username,
            'gas_login_link' => route('login')
        ]);
    }
}
