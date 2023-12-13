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
        return 'object';
    }

    public function default()
    {
        return (object) [
            'enabled' => false,
            'limit' => 0,
        ];
    }

    public function handleSave($gas, $request)
    {
        if ($request->has('enable_restrict_booking_to_credit')) {
            $restriction_info = (object) [
                'enabled' => true,
                'limit' => $request->input('restrict_booking_to_credit->limit', 0),
            ];
        }
        else {
            $restriction_info = (object) [
                'enabled' => false,
                'limit' => 0,
            ];
        }

        $gas->setConfig('restrict_booking_to_credit', $restriction_info);
    }
}
