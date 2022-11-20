<?php

namespace App\Notifications;

class RemindOrderNotification extends ManyMailNotification
{
    use MailFormatter, MailReplyTo;

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        $orders_list = '';

        foreach($this->orders as $order) {
            $row = $order->supplier->name . "\n";

            if (filled($order->comment ?? '')) {
                $row .= $order->comment . "\n";
            }

            $contacts = [];

            foreach($order->enforcedContacts() as $user) {
                $contacts[] = $user->email;
            }

            if (empty($contacts) == false) {
                $row .= _i('Per informazioni: %s', [join(', ', array_filter($contacts))]) . "\n";
            }

            $row .= $order->getBookingURL() . "\n";

            $orders_list .= $row . "\n";
        }

        $message = $this->formatMail($message, 'order_reminder', [
            'orders_list' => $orders_list,
        ]);

        return $message;
    }
}
