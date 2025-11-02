<?php

namespace App\Singletons;

use App\Booking;

class AllBookings
{
    private $allPending = null;

    public function allPendingBookings()
    {
        if ($this->allPending == null) {
            $this->allPending = Booking::where('status', 'pending')->whereHas('order', function ($query) {
                $query->whereIn('status', ['open', 'closed']);
            })->angryload()->with(['order', 'products.booking', 'products.booking.order', 'products.booking.order.products'])->get();
        }

        return $this->allPending;
    }
}
