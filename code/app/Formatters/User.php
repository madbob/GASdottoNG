<?php

namespace App\Formatters;

use App;

use App\Contact;

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

        if ($current_gas->hasFeature('shipping_places')) {
            $ret['shipping_place'] = (object) [
                'name' => _i('Luogo di Consegna'),
                'checked' => true,
                'format' => function ($obj, $context) {
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
            ];

            $ret['rid->id'] = (object) [
                'name' => _i('Mandato SEPA'),
            ];

            $ret['rid->date'] = (object) [
                'name' => _i('Data Mandato SEPA'),
            ];
        }

        /*
            Se sono nel contesto di una richiesta non vincolata a nessun GAS
            dell'istanza (cfr. middleware ActIntoGas), permetto di filtrare gli
            utenti anche in base del GAS di appartenenza
        */
        if (App::make('GlobalScopeHub')->enabled() === false) {
            $ret['gas'] = (object) [
                'name' => _i('GAS'),
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
                'name' => _i('Ultimo Accesso'),
                'format' => function ($obj, $context) {
                    return $obj->last_login;
                },
            ];

            $ret['last_booking'] = (object) [
                'name' => _i('Ultima Prenotazione'),
                'format' => function ($obj, $context) {
                    return $obj->last_booking;
                },
            ];

            $ret['member_since'] = (object) [
                'name' => _i('Membro da'),
                'format' => function ($obj, $context) {
                    return $obj->member_since;
                },
            ];

            $ret['birthplace'] = (object) [
                'name' => _i('Luogo di Nascita'),
                'format' => function ($obj, $context) {
                    return $obj->birthplace;
                },
            ];

            $ret['birthday'] = (object) [
                'name' => _i('Data di Nascita'),
                'format' => function ($obj, $context) {
                    return $obj->birthday;
                },
            ];
        }

        if ($type == 'shipping' || $type == 'all') {
            $ret['credit'] = (object) [
                'name' => _i('Credito Attuale'),
                'format' => function ($obj, $context) {
                    return printablePriceCurrency($obj->currentBalanceAmount());
                },
            ];

            $ret['other_shippings'] = (object) [
                'name' => _i('Altre Prenotazioni'),
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
                'name' => _i('Cognome'),
                'checked' => true,
            ],
            'firstname' => (object) [
                'name' => _i('Nome'),
                'checked' => true,
            ],
            'fullname' => (object) [
                'name' => _i('Nome Completo'),
                'format' => function ($obj, $context) {
                    return $obj->printableName();
                },
            ],
            'username' => (object) [
                'name' => _i('Username'),
            ],
            'taxcode' => (object) [
                'name' => _i('Codice Fiscale'),
            ],
            'card_number' => (object) [
                'name' => _i('Numero Tessera'),
            ],
            'status' => (object) [
                'name' => _i('Stato'),
                'format' => function ($obj, $context) {
                    return $obj->printableStatus();
                },
            ],
            'payment_method' => (object) [
                'name' => _i('Modalità Pagamento'),
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
