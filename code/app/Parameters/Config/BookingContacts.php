<?php

namespace App\Parameters\Config;

class BookingContacts extends Config
{
    public function identifier()
    {
        return 'booking_contacts';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return 'none';
    }
}
