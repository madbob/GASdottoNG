<?php

namespace App\Notifications;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;
use App\Notifications\Concerns\TemporaryFiles;

class ReceiptForward extends ManyMailNotification
{
    use MailFormatter, TemporaryFiles;

    private $tempFile = null;

    public function __construct($temp_file)
    {
        $this->tempFile = $temp_file;
        $this->setFiles([$temp_file]);
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, $notifiable, 'receipt')->attach($this->tempFile);
    }
}
