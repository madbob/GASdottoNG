<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

use App\User;
use App\Booking;

class BookingDelivered
{
    use SerializesModels;

    public $booking;
    public $status;
    public $user;

    public function __construct(Booking $booking, $status, User $user)
    {
        $this->booking = $booking;
        $this->status = $status;
        $this->user = $user;
    }
}
