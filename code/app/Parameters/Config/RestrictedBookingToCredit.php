<?php

namespace App\Parameters\Config;

class RestrictedBookingToCredit extends Config
{
    public function identifier()
    {
        return 'restrict_booking_to_credit';
    }

    public function type()
    {
        return 'boolean';
    }

    public function default()
    {
        return 0;
    }
}
