<?php

namespace App\View\Icons;

use App\Role;
use App\View\Icons\Concerns\Status;
use App\View\Icons\Concerns\UserGroups;

class User extends IconsMap
{
    use Status, UserGroups;

    public static function commons($user)
    {
        $ret = [];

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas)) {
            $ret['wallet'] = (object) [
                'test' => function ($obj) {
                    return $obj->currentBalanceAmount(defaultCurrency()) < 0;
                },
                'text' => __('texts.user.credit_below_zero'),
            ];

            /*
                Se la gestione delle quote di iscrizione è abilitata, viene
                attivata la relativa icona per distinguere gli utenti che non
                l'hanno pagata o rinnovata
            */
            if ($user->gas->getConfig('annual_fee_amount') != 0) {
                $ret['currency-euro'] = (object) [
                    'test' => function ($obj) {
                        return $obj->expiredFee();
                    },
                    'text' => __('texts.user.fee_not_payed'),
                ];
            }
        }

        if ($user->gas->hasFeature('public_registrations') && $user->gas->public_registrations['manual']) {
            $ret['hourglass'] = (object) [
                'test' => function ($obj) {
                    return $obj->pending;
                },
                'text' => __('texts.generic.waiting'),
            ];
        }

        return $ret;
    }

    public static function selective()
    {
        $ret = [];

        $ret['person-circle'] = (object) [
            'text' => __('texts.permissions.role'),
            'assign' => function ($obj) {
                $ret = [];
                foreach ($obj->roles as $r) {
                    $ret[] = 'hidden-person-circle-' . $r->id;
                }

                return $ret;
            },
            'options' => function ($objs) {
                $skip_roles = [];

                foreach (['user', 'friend'] as $r) {
                    $srole = roleByFunction($r);
                    if ($srole) {
                        $skip_roles[] = $srole->id;
                    }
                }

                return Role::whereNotIn('id', $skip_roles)->get()->reduce(function ($carry, $item) {
                    $carry['hidden-person-circle-' . $item->id] = $item->name;

                    return $carry;
                }, []);
            },
        ];

        $groups = self::selectiveGroups();

        return array_merge($ret, $groups);
    }
}
