<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;
use App\Notifications\MailFormatter;

class ReceiptForward extends ManyMailNotification
{
    use MailFormatter;

    private $temp_file = null;

    public function __construct($temp_file)
    {
        $this->temp_file = $temp_file;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        return $this->formatMail($message, 'receipt')->attach($this->temp_file);
    }
}
