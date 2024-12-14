<?php

namespace App\Notifications;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class ClosedOrdersNotification extends ManyMailNotification
{
    use MailFormatter;

    private $orders;

    private $files;

    public function __construct($orders, $files)
    {
        $this->orders = $orders;
        $this->files = $files;

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
        $message->subject(_i('Ordini chiusi automaticamente'))->view('emails.closedorder', ['orders' => $this->orders]);

        foreach ($this->files as $file) {
            $message->attach($file);
        }

        return $message;
    }
}
