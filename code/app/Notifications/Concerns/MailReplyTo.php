<?php

namespace App\Notifications\Concerns;

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

            foreach ($roles as $role) {
                $contacts = $role->usersByTarget($order->supplier);
                if ($contacts->isEmpty() == false) {
                    break;
                }
            }
        }

        /*
            Posso mettere un solo Reply-To alle mail, dunque pesco un contatto a
            caso tra quelli validi
        */
        foreach ($contacts->shuffle() as $c) {
            if (filled($c->email)) {
                return $c;
            }
        }

        return null;
    }

    public function guessReplyTo($message, $from)
    {
        try {
            $reply = null;

            if (is_a($from, Aggregate::class)) {
                foreach ($from->orders->shuffle() as $order) {
                    $reply = $this->guessByOrder($order);
                    if ($reply) {
                        break;
                    }
                }
            }
            elseif (is_a($from, Order::class)) {
                $reply = $this->guessByOrder($from);
            }

            if ($reply) {
                $message->replyTo($reply->email);
            }
        }
        catch (\Exception $e) {
            \Log::error('Unable to assign reply-to to notification: ' . $e->getMessage());
        }

        return $message;
    }
}
