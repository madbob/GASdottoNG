<?php

namespace App\Formatters;

use App;

use App\Contact;
use App\Group;

class User extends Formatter
{
    private static function formatContact($obj, $type)
    {
        $contacts = $obj->getContactsByType($type);

        return implode(', ', $contacts);
    }

    private static function columnsForContacts($ret)
    {
        foreach (Contact::types() as $identifier => $name) {
            $ret[$identifier] = (object) [
                'name' => $name,
                'format' => function ($obj, $context) use ($identifier) {
                    return self::formatContact($obj, $identifier);
                },
            ];
        }

        return $ret;
    }

    private static function columnsByFeatures($ret)
    {
        $current_gas = currentAbsoluteGas();

        $groups = Group::where('context', 'user')->get();
        foreach ($groups as $group) {
            $ret['group_' . $group->id] = (object) [
                'name' => __('user.formatted_aggregation', ['name' => $group->name]),
                'checked' => true,
                'format' => function ($obj, $context) use ($group) {
                    return implode(' - ', array_map(fn ($c) => $c->printableName(), $obj->circlesByGroup($group)->circles));
                },
            ];
        }

        if ($current_gas->hasFeature('rid')) {
            $ret['rid->iban'] = (object) [
                'name' => __('generic.iban'),
            ];

            $ret['rid->id'] = (object) [
                'name' => __('user.sepa.mandate'),
            ];

            $ret['rid->date'] = (object) [
                'name' => __('user.sepa.date'),
            ];
        }

        /*
            Se sono nel contesto di una richiesta non vincolata a nessun GAS
            dell'istanza (cfr. middleware ActIntoGas), permetto di filtrare gli
            utenti anche in base del GAS di appartenenza
        */
        if (App::make('GlobalScopeHub')->enabled() === false) {
            $ret['gas'] = (object) [
                'name' => __('generic.gas'),
                'format' => function ($obj, $context) {
                    return $obj->gas->name;
                },
            ];
        }

        return $ret;
    }

    private static function columnsByType($ret, $type)
    {
        if ($type == 'export' || $type == 'all') {
            $ret['last_login'] = (object) [
                'name' => __('user.last_login'),
                'format' => function ($obj, $context) {
                    return $obj->last_login;
                },
            ];

            $ret['last_booking'] = (object) [
                'name' => __('user.last_booking'),
                'format' => function ($obj, $context) {
                    return $obj->last_booking;
                },
            ];

            $ret['member_since'] = (object) [
                'name' => __('user.member_since'),
                'format' => function ($obj, $context) {
                    return $obj->member_since;
                },
            ];

            $ret['birthplace'] = (object) [
                'name' => __('user.birthplace'),
                'format' => function ($obj, $context) {
                    return $obj->birthplace;
                },
            ];

            $ret['birthday'] = (object) [
                'name' => __('user.birthdate'),
                'format' => function ($obj, $context) {
                    return $obj->birthday;
                },
            ];
        }

        if ($type == 'shipping' || $type == 'all') {
            $ret['credit'] = (object) [
                'name' => __('movements.current_credit'),
                'format' => function ($obj, $context) {
                    return printablePriceCurrency($obj->currentBalanceAmount());
                },
            ];

            $ret['other_shippings'] = (object) [
                'name' => __('user.other_bookings'),
                'format' => function ($obj, $context) {
                    /*
                        Qui, $context deve essere un Aggregate
                    */
                    return $obj->morePendingBookings($context) ?: '';
                },
            ];
        }

        return $ret;
    }

    public static function formattableColumns($type = null)
    {
        $ret = [
            'lastname' => (object) [
                'name' => __('user.lastname'),
                'checked' => true,
            ],
            'firstname' => (object) [
                'name' => __('user.firstname'),
                'checked' => true,
            ],
            'fullname' => (object) [
                'name' => __('user.fullname'),
                'format' => function ($obj, $context) {
                    return $obj->printableName();
                },
            ],
            'username' => (object) [
                'name' => __('auth.username'),
            ],
            'taxcode' => (object) [
                'name' => __('user.taxcode'),
            ],
            'card_number' => (object) [
                'name' => __('user.card_number'),
            ],
            'status' => (object) [
                'name' => __('generic.status'),
                'format' => function ($obj, $context) {
                    return $obj->printableStatus();
                },
            ],
            'payment_method' => (object) [
                'name' => __('user.payment_method'),
                'format' => function ($obj, $context) {
                    return $obj->payment_method->name;
                },
            ],
        ];

        $ret = self::columnsForContacts($ret);
        $ret = self::columnsByFeatures($ret);
        $ret = self::columnsByType($ret, $type);

        return $ret;
    }
}
