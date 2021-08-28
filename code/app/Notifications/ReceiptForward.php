<?php

namespace App\Notifications;

class ReceiptForward extends ManyMailNotification
{
    use MailFormatter, TemporaryFiles;

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
