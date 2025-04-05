<?php

namespace App\View\Icons;

use App\View\Icons\Concerns\BookingStatus;

class Booking extends IconsMap
{
    use BookingStatus;

    public static function commons($user)
    {
        return self::bookingStatusIcons([]);
    }
}
