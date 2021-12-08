<?php

namespace App\Notifications;

use App\Aggregate;

class BookingNotification extends ManyMailNotification
{
    use MailReplyTo;

    private $aggregate_id = null;
    private $user_id = null;
    private $message = null;

    public function __construct($aggregate_id, $user_id, $message)
    {
        $this->aggregate_id = $aggregate_id;
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
            'txt_message' => $this->message
        ]);

        $message = $this->guessReplyTo($message, $aggregate);

        return $message;
    }
}
