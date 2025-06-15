<?php

namespace App\View\Icons\Concerns;

trait BookingStatus
{
    public static function bookingStatusIcons($ret)
    {
        $ret['clock'] = (object) [
            'test' => function ($obj) {
                return $obj->status != 'shipped';
            },
            'text' => __('orders.booking.statuses.to_deliver'),
        ];

        $ret['check'] = (object) [
            'test' => function ($obj) {
                return $obj->status == 'shipped';
            },
            'text' => __('orders.booking.statuses.shipped'),
        ];

        $ret['save'] = (object) [
            'test' => function ($obj) {
                return $obj->status == 'saved';
            },
            'text' => __('orders.booking.statuses.saved'),
        ];

        return $ret;
    }
}
