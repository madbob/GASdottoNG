<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReceiptForward extends ManyMailNotification implements ShouldQueue
{
    use MailFormatter, TemporaryFiles, Queueable;

    private $temp_file = null;

    public function __construct($temp_file)
    {
        $this->temp_file = $temp_file;
        $this->setFiles($temp_file);
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        return $this->formatMail($message, 'receipt')->attach($this->temp_file);
    }
}
