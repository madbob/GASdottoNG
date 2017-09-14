<?php

namespace App\Notifications;

use App\Notifications\ManyMailNotification;

class BookingNotification extends ManyMailNotification
{
    private $booking = null;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject('Riassunto prenotazione del GAS')->view('emails.booking', ['booking' => $this->booking]);
        return $message;
    }
}
