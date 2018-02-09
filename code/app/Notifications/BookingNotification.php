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
        $message->subject(_i('Riassunto prenotazione del GAS'))->view('emails.booking', ['booking' => $this->booking, 'txt_message' => $this->message]);
        return $message;
    }
}
