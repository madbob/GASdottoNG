<?php

namespace App\Formatters;

use App;

class User extends Formatter
{
    private static function formatContact($obj, $type)
    {
        $contacts = $obj->getContactsByType($type);
        return join(', ', $contacts);
    }

    public static function formattableColumns($type = null)
    {
        $ret = [
            'lastname' => (object) [
                'name' => _i('Cognome'),
                'checked' => true,
            ],
            'firstname' => (object) [
                'name' => _i('Nome'),
                'checked' => true,
            ],
            'fullname' => (object) [
                'name' => _i('Nome Completo'),
                'format' => function($obj, $context) {
                    return $obj->printableName();
                },
            ],
            'username' => (object) [
                'name' => _i('Username'),
            ],
            'email' => (object) [
                'name' => _i('E-Mail'),
                'format' => function($obj, $context) {
                    return self::formatContact($obj, 'email');
                },
            ],
            'phone' => (object) [
                'name' => _i('Telefono'),
                'format' => function($obj, $context) {
                    return self::formatContact($obj, 'phone');
                },
            ],
            'mobile' => (object) [
                'name' => _i('Cellulare'),
                'format' => function($obj, $context) {
                    return self::formatContact($obj, 'mobile');
                },
            ],
            'address' => (object) [
                'name' => _i('Indirizzo'),
                'format' => function($obj, $context) {
                    return self::formatContact($obj, 'address');
                },
            ],
            'taxcode' => (object) [
                'name' => _i('Codice Fiscale'),
            ],
            'card_number' => (object) [
                'name' => _i('Numero Tessera'),
            ],
            'status' => (object) [
                'name' => _i('Stato'),
                'format' => function($obj, $context) {
                    return $obj->printableStatus();
                },
            ],
            'payment_method' => (object) [
                'name' => _i('Modalità Pagamento'),
                'format' => function($obj, $context) {
                    return $obj->payment_method->name;
                },
            ],
        ];

        $current_gas = currentAbsoluteGas();

        if ($current_gas->hasFeature('shipping_places')) {
            $ret['shipping_place'] = (object) [
                'name' => _i('Luogo di Consegna'),
                'checked' => true,
                'format' => function($obj, $context) {
                    $sp = $obj->shippingplace;
                    if (is_null($sp)) {
                        return _i('Nessuno');
                    }
                    else {
                        return $sp->name;
                    }
                },
            ];
        }

        if ($current_gas->hasFeature('rid')) {
            $ret['rid->iban'] = (object) [
                'name' => _i('IBAN'),
                'checked' => true,
            ];
        }

        /*
            Se sono nel contesto di una richiesta non vincolata a nessun GAS
            dell'istanza (cfr. middleware ActIntoGas), permetto di filtrare gli
            utenti anche in base del GAS di appartenenza
        */
        if (App::make('GlobalScopeHub')->enabled() == false) {
            $ret['gas'] = (object) [
                'name' => _i('GAS'),
                'format' => function($obj, $context) {
                    return $obj->gas->name;
                },
            ];
        }

        return $ret;
    }
}
