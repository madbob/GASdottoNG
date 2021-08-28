<?php

namespace App\Notifications;

use Auth;

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

        /*
            Reminder: i files qui allegati non vanno rimossi subito dopo l'invio
            della notifica in quanto possono essere usati molteplici volte, per
            tutti i referenti dell'ordine. Vengono semmai rimossi a posteriori,
            una volta sola. Cfr. NotifyClosedOrder
        */
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(_i('Ordine chiuso automaticamente'))->view('emails.closedorder', ['order' => $this->order])->attach($this->pdf_file)->attach($this->csv_file);
        return $message;
    }
}
