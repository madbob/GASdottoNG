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

    private $aggregate_id = null;

    private $redux = null;

    private $user_id = null;

    private $message = null;

    public function __construct($aggregate_id, $redux, $user_id, $message)
    {
        $this->aggregate_id = $aggregate_id;
        $this->redux = $redux;
        $this->user_id = $user_id;
        $this->message = $message;
    }

    public function toMail($notifiable)
    {
        $aggregate = Aggregate::find($this->aggregate_id);
        $booking = $aggregate->bookingBy($this->user_id);

        $message = $this->initMailMessage($notifiable);
        $strings = $booking->convenient_strings;

        $message->subject(_i('Riassunto prenotazione del GAS: %s - consegna %s', [$strings['suppliers'], $strings['shipping']]))->view('emails.booking', [
            'booking' => $booking,
            'redux' => $this->redux,
            'txt_message' => $this->message,
        ]);

        $message = $this->guessReplyTo($message, $aggregate);

        return $message;
    }
}
