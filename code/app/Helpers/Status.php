<?php

namespace App\Helpers;

class Status
{
    public static function orders()
    {
        static $ret = null;

        if (is_null($ret)) {
            /*
                L'attributo "default_display" determina gli stati che vengono
                visualizzati di default quando viene chiesto l'elenco degli ordini.
                Cfr. defaultOrders()

                L'attributo "aggregate_priority" serve a determinare lo stato
                dell'aggregato dentro cui si trova l'ordine stesso: lo stato di
                priorità più bassa vince. Cfr. Aggregate::getStatusAttribute()
            */

            $statuses = [];

            $statuses['open'] = (object) [
                'label' => __('texts.orders.booking.statuses.open'),
                'icon' => 'play',
                'default_display' => true,
                'aggregate_priority' => 1,
            ];

            $statuses['closed'] = (object) [
                'label' => __('texts.orders.booking.statuses.closed'),
                'icon' => 'stop-fill',
                'default_display' => true,
                'aggregate_priority' => 2,
            ];

            $statuses['shipped'] = (object) [
                'label' => __('texts.orders.booking.statuses.shipped'),
                'icon' => 'skip-forward',
                'default_display' => true,
                'aggregate_priority' => 4,
            ];

            if (currentAbsoluteGas()->hasFeature('integralces')) {
                $statuses['user_payment'] = (object) [
                    'label' => __('texts.orders.booking.statuses.paying'),
                    'icon' => 'cash',
                    'default_display' => true,
                    'aggregate_priority' => 3,
                ];
            }

            $statuses['archived'] = (object) [
                'label' => __('texts.orders.booking.statuses.archived'),
                'icon' => 'eject',
                'default_display' => false,
                'aggregate_priority' => 5,
            ];

            $statuses['suspended'] = (object) [
                'label' => __('texts.orders.booking.statuses.suspended'),
                'icon' => 'pause',
                'default_display' => true,
                'aggregate_priority' => 0,
            ];

            $ret = $statuses;
        }

        return $ret;
    }

    public static function invoices()
    {
        return [
            'pending' => (object) [
                'label' => __('texts.generic.waiting'),
                'icon' => 'clock',
            ],
            'to_verify' => (object) [
                'label' => __('texts.invoices.statuses.to_verify'),
                'icon' => 'pin-angle',
            ],
            'verified' => (object) [
                'label' => __('texts.invoices.statuses.verified'),
                'icon' => 'search',
            ],
            'payed' => (object) [
                'label' => __('texts.invoices.statuses.payed'),
                'icon' => 'check',
            ],
        ];
    }
}
