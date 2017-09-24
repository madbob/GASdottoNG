<?php

namespace App\Listeners;

use App\Events\BookingDeleting;

class BookingModel
{
    public function __construct()
    {
        //
    }

    public function handle(BookingDeleting $event)
    {
        if ($event->booking->status == 'shipped')
            return false;

        $event->booking->deleteMovements();
    }
}
