<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class BookingDeleting
{
    use Dispatchable, SerializesModels;

    public $booking;

    public function __construct(Model $booking)
    {
        $this->booking = $booking;
    }
}
