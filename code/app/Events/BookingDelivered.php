<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

use App\User;
use App\Booking;

class BookingDelivered
{
    use Dispatchable, SerializesModels;

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
