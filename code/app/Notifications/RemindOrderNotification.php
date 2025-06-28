<?php

namespace App\Notifications;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;
use App\Notifications\Concerns\MailReplyTo;

class RemindOrderNotification extends ManyMailNotification
{
    use MailFormatter, MailReplyTo;

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    private function formatOrder($order)
    {
        $row = $order->supplier->name . "\n";

        if (filled($order->comment)) {
            $row .= $order->comment . "\n";
        }

        $contacts = [];

        foreach ($order->enforcedContacts() as $user) {
            $contacts[] = $user->email;
        }

        if (empty($contacts) === false) {
            $row .= __('texts.mail.contacts_prefix', [
                'contacts' => implode(', ', array_filter($contacts)),
            ]) . "\n";
        }

        $row .= $order->getBookingURL() . "\n";

        return $row;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        $orders_list = '';
        $closing_date = null;
        $suppliers_list = [];

        foreach ($this->orders as $order) {
            $row = $this->formatOrder($order);
            $orders_list .= $row . "\n";

            $suppliers_list[] = $order->supplier->printableName();

            if ($closing_date === null) {
                $closing_date = printableDate($order->end);
            }
        }

        $message = $this->formatMail($message, $notifiable, 'order_reminder', [
            'closing_date' => $closing_date,
            'orders_list' => $orders_list,
            'suppliers_list' => join(', ', $suppliers_list),
        ]);

        return $message;
    }
}
