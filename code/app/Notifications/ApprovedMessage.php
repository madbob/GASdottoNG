<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovedMessage extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, Queueable;

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        return $this->formatMail($message, $notifiable, 'approved');
    }
}
