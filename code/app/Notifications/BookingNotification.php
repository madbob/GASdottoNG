<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;

class BookingNotification extends ManyMailNotification
{
    private $booking = null;
    private $message = null;

    public function __construct($booking, $message)
    {
        $this->booking = $booking;
        $this->message = $message;
    }

    public function toMail($notifiable)
    {
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable, $user);
        $strings = $this->booking->convenient_strings;
        $message->subject(_i('Riassunto prenotazione del GAS: %s - consegna %s', [$strings['suppliers'], $strings['shipping']]))->view('emails.booking', ['booking' => $this->booking, 'txt_message' => $this->message]);
        return $message;
    }
}
