<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;

class NewOrderNotification extends ManyMailNotification
{
    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function toMail($notifiable)
    {
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable, $user);
        $message->subject(_i('Nuovo Ordine Aperto per %s', [$this->order->supplier->name]))->view('emails.new_order', ['order' => $this->order]);
        return $message;
    }
}
