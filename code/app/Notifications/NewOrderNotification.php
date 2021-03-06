<?php

namespace App\Notifications;

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
        $message = $this->initMailMessage($notifiable);

        $contacts = [];
        foreach($this->order->enforcedContacts() as $user) {
            $contacts[] = $user->email;
        }
        $contacts = join(', ', array_filter($contacts));

        return $this->formatMail($message, 'new_order', [
            'supplier_name' => $this->order->supplier->name,
            'order_comment' => $this->order->comment ?? '',
            'gas_booking_link' => $this->order->getBookingURL(),
            'contacts' => $contacts,
            'closing_date' => printableDate($this->order->end)
        ]);
    }
}
