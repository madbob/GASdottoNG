<?php

namespace App\View\Icons;

use App\View\Icons\Concerns\BookingStatus;
use App\View\Icons\Concerns\UserGroups;

use App\Group;

class AggregateBooking extends IconsMap
{
    use BookingStatus, UserGroups;

    private static $bookinggroups = null;

    public static function commons($user)
    {
        return self::bookingStatusIcons([]);
    }

    public static function selective()
    {
        if (static::$bookinggroups == null) {
            $groups = Group::orderBy('name', 'asc')->where('context', 'booking')->get();
            if ($groups->isEmpty() === false) {
                static::$bookinggroups['truck'] = (object) [
                    'text' => __('orders.booking_aggregation'),
                    'assign' => function ($obj) {
                        $ret = [];

                        if ($obj->booking_circles->isEmpty()) {
                            $ret[] = 'hidden-truck-none';
                        }
                        else {
                            foreach ($obj->booking_circles as $c) {
                                $ret[] = 'hidden-truck-' . $c->id;
                            }
                        }

                        return $ret;
                    },
                    'options' => function ($objs) use ($groups) {
                        $ret = [];

                        $ret['hidden-truck-none'] = __('user.without_aggregation');

                        foreach ($groups as $group) {
                            foreach ($group->circles()->orderBy('name', 'asc')->get() as $circle) {
                                $ret['hidden-truck-' . $circle->id] = sprintf('%s - %s', $group->printableName(), $circle->printableName());
                            }
                        }

                        return $ret;
                    },
                ];
            }
        }

        $usergroups = self::selectiveGroups();

        return array_merge(static::$bookinggroups ?: [], $usergroups ?: []);
    }
}
