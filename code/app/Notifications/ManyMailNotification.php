<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

use App\Models\Concerns\ContactableTrait;

/*
    Di norma le notifiche mail vanno a leggere il campo "email" dell'oggetto da
    notificare, ma nel nostro caso i contatti sono da un'altra parte e possono
    essere molteplici.
    Le classi per le notifiche che estendono questa qua vanno a popolare i
    destinatari delle mail tenendo conto di questo.
*/
class ManyMailNotification extends Notification
{
    public function via($notifiable)
    {
        $this->connection = config('queue.default');
        return ['mail'];
    }

    private function attachReplyTo($message, $replyTo)
    {
        if (is_string($replyTo)) {
            $message->replyTo($replyTo);
        }
        else {
            if (!empty($replyTo->email)) {
                $message->replyTo($replyTo->email);
            }
        }

        return $message;
    }

    protected function initMailMessage($notifiable, $replyTo = null)
    {
        $message = new MailMessage();

        if (in_array(ContactableTrait::class, class_uses(get_class($notifiable)))) {
            $notifiable->messageAll($message);
        }

        if (!empty($replyTo)) {
            $message = $this->attachReplyTo($message, $replyTo);
        }

        return $message;
    }
}
