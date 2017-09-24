<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class BookingDeleting
{
    use SerializesModels;

    public $booking;

    public function __construct(Model $booking)
    {
        $this->booking = $booking;
    }
}
