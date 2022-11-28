<?php

namespace App\Observers;

use Log;

use App\Booking;

class BookingObserver
{
    public function deleting(Booking $booking)
    {
        Log::debug('Elimino prenotazione ' . $booking->id);
        return true;
    }
}
