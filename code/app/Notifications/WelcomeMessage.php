<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class WelcomeMessage extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, $notifiable, 'welcome');
    }
}
