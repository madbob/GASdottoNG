<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;

class ClosedOrderNotification extends ManyMailNotification
{
    use MailFormatter;

    private $order;
    private $pdf_file;
    private $csv_file;

    public function __construct($order, $pdf_file, $csv_file)
    {
        $this->order = $order;
        $this->pdf_file = $pdf_file;
        $this->csv_file = $csv_file;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(_i('Ordine chiuso automaticamente'))->view('emails.closedorder', ['order' => $this->order])->attach($this->pdf_file)->attach($this->csv_file);
        return $message;
    }
}
