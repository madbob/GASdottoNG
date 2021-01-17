<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;
use App\Notifications\MailFormatter;

class NewOrderNotification extends ManyMailNotification
{
    use MailFormatter;

    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function toMail($notifiable)
    {
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable, $user);

        return $this->formatMail($message, 'new_order', [
            'supplier_name' => $this->order->supplier->name,
            'order_comment' => $this->order->comment ?? '',
            'gas_booking_link' => $this->order->getBookingURL(),
            'closing_date' => printableDate($this->order->end)
        ]);
    }
}
