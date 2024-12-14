<?php

namespace App\View\Icons;

class AggregateBooking extends IconsMap
{
    public static function commons($user)
    {
        return [
            'clock' => (object) [
                'test' => function ($obj) {
                    return $obj->status != 'shipped';
                },
                'text' => _i('Da consegnare'),
            ],
            'check' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'shipped';
                },
                'text' => _i('Consegnato'),
            ],
            'save' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'saved';
                },
                'text' => _i('Salvato'),
            ],
        ];
    }

    public static function selective()
    {
        if (currentAbsoluteGas()->hasFeature('shipping_places')) {
            return [
                'truck' => (object) [
                    'text' => _i('Luogo di Consegna'),
                    'assign' => function ($obj) {
                        return [
                            'hidden-truck-' . $obj->user->preferred_delivery_id,
                        ];
                    },
                    'options' => function ($objs) {
                        $ret = [];

                        foreach ($objs as $obj) {
                            $key = 'hidden-truck-' . $obj->user->preferred_delivery_id;
                            if (isset($ret[$key]) == false) {
                                $place = $obj->user->shippingplace;
                                if ($place) {
                                    $ret[$key] = $place->name;
                                }
                            }
                        }

                        return $ret;
                    },
                ],
            ];
        }
        else {
            return [];
        }
    }
}
