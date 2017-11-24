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
        $message = $this->initMailMessage($notifiable);
        $message->subject('Riassunto prenotazione del GAS')->replyTo($user)->view('emails.booking', ['booking' => $this->booking, 'txt_message' => $this->message]);
        return $message;
    }
}
