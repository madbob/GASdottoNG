<?php

namespace App\Notifications;

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
        $message = $this->initMailMessage($notifiable);
        $message->subject('Riassunto prenotazione del GAS')->view('emails.booking', ['booking' => $this->booking, 'message' => $this->message]);
        return $message;
    }
}
