<?php

namespace App\View\Icons;

use Auth;

use App\Role;

class User extends IconsMap
{
    public static function commons($user)
    {
        $ret = [];

        if ($user->can('users.admin', $user->gas)) {
            $ret['hand-thumbs-down'] = (object) [
                'test' => function ($obj) {
                    return !is_null($obj->suspended_at);
                },
                'text' => _i('Sospeso'),
            ];

            $ret['slash-circle'] = (object) [
                'test' => function ($obj) {
                    return !is_null($obj->deleted_at);
                },
                'text' => _i('Cessato'),
            ];
        }

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas)) {
            $ret['wallet'] = (object) [
                'test' => function ($obj) {
                    return $obj->current_balance_amount < 0;
                },
                'text' => _i('Credito < 0'),
            ];

            /*
                Se la gestione delle quote di iscrizione è abilitata, viene
                attivata la relativa icona per distinguere gli utenti che non
                l'hanno pagata o rinnovata
            */
            if ($user->gas->getConfig('annual_fee_amount') != 0) {
                $ret['currency-euro'] = (object) [
                    'test' => function ($obj) {
                        return $obj->fee_id == 0;
                    },
                    'text' => _i('Quota non Pagata'),
                ];
            }
        }

        return $ret;
    }

    public static function selective()
    {
        return [
            'person-circle' => (object) [
                'text' => _i('Ruolo'),
                'assign' => function ($obj) {
                    $ret = [];
                    foreach($obj->roles as $r) {
                        $ret[] = 'hidden-person-circle-' . $r->id;
                    }
                    return $ret;
                },
                'options' => function($objs) {
                    $user = Auth::user();

                    return Role::whereNotIn('id', [$user->gas->roles['user'], $user->gas->roles['friend']])->get()->reduce(function($carry, $item) {
                        $carry['hidden-person-circle-' . $item->id] = $item->name;
                        return $carry;
                    }, []);
                }
            ]
        ];
    }
}
