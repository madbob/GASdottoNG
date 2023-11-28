<?php

namespace App\Observers;

use App\BookedProduct;

class BookedProductObserver
{
    public function saved(BookedProduct $booked)
    {
        $booking = $booked->booking;
        $booking->updated_by = $booked->updated_by;
        $booking->save();
    }
}
