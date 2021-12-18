<?php

namespace App\Notifications;

use App\Aggregate;
use App\Order;
use App\Role;

trait MailReplyTo
{
    private function guessByOrder($order)
    {
        $contacts = $order->showableContacts();
        if ($contacts->isEmpty()) {
            $roles = Role::havingAction('supplier.orders');

            foreach($roles as $role) {
                $contacts = $role->usersByTarget($order->supplier);
                if ($contacts->isEmpty() == false) {
                    break;
                }
            }
        }

        return $contacts->random();
    }

    public function guessReplyTo($message, $from)
    {
        try {
            $reply = null;

            if (is_a($from, Aggregate::class)) {
                $order = $from->orders->random();
                $reply = $this->guessByOrder($order);
            }
            else if (is_a($from, Order::class)) {
                $reply = $this->guessByOrder($from);
            }

            if ($reply) {
                $message->replyTo($reply->email);
            }
        }
        catch(\Exception $e) {
            \Log::error('Unable to assign reply-to to notification: ' . $e->getMessage());
        }

        return $message;
    }
}
