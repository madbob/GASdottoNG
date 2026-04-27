<?php

namespace App\Observers;

use App\Booking;

class BookingObserver
{
    public function deleting(Booking $booking)
    {
        if ($booking->status == 'shipped') {
            return false;
        }

        $booking->deleteMovements();
        return true;
    }
}
