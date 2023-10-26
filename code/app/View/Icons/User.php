<?php

namespace App\View\Icons;

use Auth;

use App\Role;

class User extends IconsMap
{
    use Status;

    public static function commons($user)
    {
        $ret = [];

        if ($user->can('users.admin', $user->gas)) {
            $ret = self::statusIcons($ret);

            if ($user->gas->hasFeature('shipping_places')) {
                $ret['house-fill'] = (object) [
                    'test' => function ($obj) {
                        return $obj->preferred_delivery_id == '0';
                    },
                    'text' => _i('Senza Luogo di Consegna'),
                ];
            }
        }

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas)) {
            $ret['wallet'] = (object) [
                'test' => function ($obj) {
                    return $obj->currentBalanceAmount(defaultCurrency()) < 0;
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

        if ($user->gas->hasFeature('public_registrations') && $user->gas->public_registrations['manual']) {
            $ret['hourglass'] = (object) [
                'test' => function ($obj) {
                    return $obj->pending;
                },
                'text' => _i('In Attesa'),
            ];
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

					$skip_roles = [];

					foreach(['user', 'friend'] as $r) {
						$srole = roleByFunction($r);
						if ($srole) {
							$skip_roles[] = $srole->id;
						}
					}

                    return Role::whereNotIn('id', $skip_roles)->get()->reduce(function($carry, $item) {
                        $carry['hidden-person-circle-' . $item->id] = $item->name;
                        return $carry;
                    }, []);
                }
            ]
        ];
    }
}
