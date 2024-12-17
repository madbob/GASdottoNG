<?php

namespace App\Importers\CSV;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\User;
use App\Contact;
use App\Group;
use App\Circle;

class Users extends CSVImporter
{
    private function essentialFields(&$ret)
    {
        $ret['firstname'] = (object) [
            'label' => _i('Nome'),
            'mandatory' => true,
        ];

        $ret['lastname'] = (object) [
            'label' => _i('Cognome'),
            'mandatory' => true,
        ];

        $ret['username'] = (object) [
            'label' => _i('Username'),
            'mandatory' => true,
        ];

        $ret['password'] = (object) [
            'label' => _i('Password'),
        ];
    }

    private function contactFields(&$ret)
    {
        foreach (Contact::types() as $identifier => $label) {
            /*
                L'ordine di questi elementi deve essere coerente con l'ordine
                utilizzato in popovers.js per spezzare e visualizzare gli
                indirizzi
            */
            if ($identifier == 'address') {
                $ret['address_0'] = (object) [
                    'label' => _i('Indirizzo (Via)'),
                ];

                $ret['address_1'] = (object) [
                    'label' => _i('Indirizzo (Città)'),
                ];

                $ret['address_2'] = (object) [
                    'label' => _i('Indirizzo (CAP)'),
                ];
            }
            else {
                $ret[$identifier] = (object) [
                    'label' => $label,
                ];
            }
        }
    }

    private function otherFields(&$ret)
    {
        $ret['birthplace'] = (object) [
            'label' => _i('Luogo di Nascita'),
        ];

        $ret['birthday'] = (object) [
            'label' => _i('Data di Nascita'),
            'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')]),
        ];

        $ret['taxcode'] = (object) [
            'label' => _i('Codice Fiscale'),
        ];

        $ret['member_since'] = (object) [
            'label' => _i('Membro da'),
            'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')]),
        ];

        $ret['card_number'] = (object) [
            'label' => _i('Numero Tessera'),
        ];

        $ret['last_login'] = (object) [
            'label' => _i('Ultimo Accesso'),
            'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')]),
        ];

        $groups = Group::where('context', 'user')->orderBy('name', 'asc')->get();
        foreach ($groups as $group) {
            $ret['group_' . $group->id] = (object) [
                'label' => _i('Gruppo %s'),
                'explain' => _i('Se specificato, deve contenere il nome di uno dei Cerchi impostati nel pannello "Configurazioni" per questo Gruppo'),
            ];
        }

        $ret['ceased'] = (object) [
            'label' => _i('Cessato'),
            'explain' => _i('Indicare "true" o "false"'),
        ];

        $gas = currentAbsoluteGas();
        if ($gas->hasFeature('shipping_places')) {
            $ret['preferred_delivery_id'] = (object) [
                'label' => _i('Luogo di Consegna'),
                'explain' => _i('Se specificato, deve contenere il nome di uno dei Luoghi di Consegna impostati nel pannello "Configurazioni"'),
            ];
        }

        $ret['credit'] = (object) [
            'label' => _i('Credito Attuale'),
            'explain' => _i('Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!'),
        ];
    }

    public function fields()
    {
        $ret = [];
        $this->essentialFields($ret);
        $this->contactFields($ret);
        $this->otherFields($ret);

        return $ret;
    }

    public function testAccess($request)
    {
        $user = $request->user();

        return $user->can('users.admin', $user->gas);
    }

    public function guess($request)
    {
        return $this->storeUploadedFile($request, [
            'type' => 'users',
            'extra_description' => [
                _i('Se il login è già esistente il relativo utente sarà aggiornato coi dati letti dal file.'),
                _i('Altrimenti verrà inviata una email di invito con il link da visitare per accedere la prima volta e definire la propria password.'),
            ],
            'sorting_fields' => $this->fields(),
        ]);
    }

    public function select($request)
    {
        return null;
    }

    public function formatSelect($parameters)
    {
        return null;
    }

    private function fillContact(&$contacts, $type, $value)
    {
        if (! empty($value)) {
            $contacts['contact_id'][] = '';
            $contacts['contact_type'][] = $type;
            $contacts['contact_value'][] = $value;
        }
    }

    private function retrieveUser($login, $gas)
    {
        $u = User::where('username', '=', $login)->orderBy('id', 'desc')->first();

        if (is_null($u)) {
            $u = new User();
            $u->gas_id = $gas->id;
            $u->username = $login;
            $u->password = Hash::make($login);
            $u->member_since = date('Y-m-d');
        }

        return $u;
    }

    public function run($request)
    {
        DB::beginTransaction();

        $columns = $this->initRead($request);
        [$login_index] = $this->getColumnsIndex($columns, ['username']);

        $gas = Auth::user()->gas;
        $users = [];
        $errors = [];

        $contact_types = array_keys(Contact::types());
        $groups = Group::where('context', 'user')->orderBy('name', 'asc')->get();

        /*
            TODO: aggiornare questo per adattarlo a UsersService
        */

        foreach ($this->getRecords() as $line) {
            try {
                $new_user = false;
                $login = $line[$login_index];

                $u = $this->retrieveUser($login, $gas);
                $new_user = ($u->exists == false);

                $contacts = [
                    'contact_id' => [],
                    'contact_type' => [],
                    'contact_value' => [],
                ];

                $credit = null;
                $password_defined = false;
                $address = [];
                $assigned_circles = [];

                foreach ($columns as $index => $field) {
                    $value = (string) $line[$index];

                    if ($field == 'none') {
                        continue;
                    }
                    elseif (in_array($field, $contact_types)) {
                        $this->fillContact($contacts, $field, $value);

                        continue;
                    }
                    elseif ($field == 'password') {
                        if (filled($value)) {
                            $u->password = Hash::make($value);

                            if ($new_user == false) {
                                \Log::debug('Cambio password utente ' . $u->username . ' durante importazione CSV');
                            }

                            $password_defined = true;
                        }
                    }
                    elseif ($field == 'birthday' || $field == 'member_since' || $field == 'last_login') {
                        $u->$field = date('Y-m-d', strtotime($value));
                    }
                    elseif ($field == 'credit') {
                        if (! empty($line[$index]) && $line[$index] != 0) {
                            $credit = guessDecimal($value);
                        }
                    }
                    elseif ($field == 'ceased') {
                        if (strtolower($value) == 'true' || strtolower($value) == 'vero' || $value == '1') {
                            $u->deleted_at = date('Y-m-d');
                        }
                    }
                    elseif (Str::startsWith($field, 'address_')) {
                        $index = (int) Str::after($field, 'address_');
                        $address[$index] = $value;
                    }
                    elseif (str_starts_with($field, 'group_')) {
                        $value = trim($value);
                        $group_id = substr($field, strlen('group_'));
                        $circle = Circle::where('name', $value)->where('group_id', $group_id)->first();
                        if ($circle) {
                            $assigned_circles[] = $circle->id;
                        }
                    }
                    else {
                        $u->$field = $value;
                    }
                }

                $u->save();

                $users[] = $u;

                ksort($address);
                $this->fillContact($contacts, 'address', implode(',', $address));
                $u->updateContacts($contacts);

                $u->circles()->sync($assigned_circles);

                if ($credit != null) {
                    $u->alterBalance($credit, defaultCurrency());
                }

                if ($new_user && $password_defined == false) {
                    $u->initialWelcome();
                }
            }
            catch (\Exception $e) {
                $errors[] = implode(',', $line).'<br/>'.$e->getMessage();
            }
        }

        DB::commit();

        return [
            'title' => _i('Utenti importati'),
            'objects' => $users,
            'errors' => $errors,
        ];
    }
}
