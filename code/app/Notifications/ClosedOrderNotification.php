<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;

class ClosedOrderNotification extends ManyMailNotification
{
    private $order = null;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(_i('Ordine chiuso automaticamente'))->view('emails.closedorder', ['order' => $this->order]);
        return $message;
    }
}
