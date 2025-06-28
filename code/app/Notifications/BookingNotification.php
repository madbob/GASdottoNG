<?php

/*
    Non rendere schedulabile questa notifica: i parametri sono troppo corposi
    per essere immessi nella queue, e comunque questa notifica viene già
    generata dal job AggregateSummaries
*/

namespace App\Notifications;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailReplyTo;
use App\Aggregate;

class BookingNotification extends ManyMailNotification
{
    use MailReplyTo;

    private $aggregateId = null;

    private $redux = null;

    private $userId = null;

    private $message = null;

    public function __construct($aggregate_id, $redux, $user_id, $message)
    {
        $this->aggregateId = $aggregate_id;
        $this->redux = $redux;
        $this->userId = $user_id;
        $this->message = $message;
    }

    public function toMail($notifiable)
    {
        $aggregate = Aggregate::find($this->aggregateId);
        $booking = $aggregate->bookingBy($this->userId);

        $message = $this->initMailMessage($notifiable);
        $strings = $booking->convenient_strings;

        $message->subject(__('texts.mail.summary.defaults.subject', [
            'supplier' => $strings['suppliers'],
            'delivery' => $strings['shipping'],
        ]))->view('emails.booking', [
            'booking' => $booking,
            'redux' => $this->redux,
            'txt_message' => $this->message,
        ]);

        $message = $this->guessReplyTo($message, $aggregate);

        return $message;
    }
}
