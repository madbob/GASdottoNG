<?php

namespace App\Importers\CSV;

use Auth;
use App;
use DB;
use Hash;

use App\User;

class Users extends CSVImporter
{
    protected function fields()
    {
        return [
            'firstname' => (object) [
                'label' => _i('Nome'),
            ],
            'lastname' => (object) [
                'label' => _i('Cognome'),
            ],
            'username' => (object) [
                'label' => _i('Login'),
                'mandatory' => true
            ],
            'email' => (object) [
                'label' => _i('E-Mail'),
            ],
            'phone' => (object) [
                'label' => _i('Telefono'),
            ],
            'mobile' => (object) [
                'label' => _i('Cellulare'),
            ],
            'address_street' => (object) [
                'label' => _i('Indirizzo (Via)'),
            ],
            'address_zip' => (object) [
                'label' => _i('Indirizzo (CAP)'),
            ],
            'address_city' => (object) [
                'label' => _i('Indirizzo (Città)'),
            ],
            'birthday' => (object) [
                'label' => _i('Data di Nascita'),
                'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
            ],
            'taxcode' => (object) [
                'label' => _i('Codice Fiscale'),
            ],
            'member_since' => (object) [
                'label' => _i('Membro da'),
                'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
            ],
            'last_login' => (object) [
                'label' => _i('Ultimo Accesso'),
                'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
            ],
            'ceased' => (object) [
                'label' => _i('Cessato'),
                'explain' => _i('Indicare "true" o "false"')
            ],
            'credit' => (object) [
                'label' => _i('Credito Attuale'),
                'explain' => _i('Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!')
            ]
        ];
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

    public function run($request)
    {
        DB::beginTransaction();

        list($reader, $columns) = $this->initRead($request);
        list($login_index) = $this->getColumnsIndex($columns, ['username']);
        $target_separator = ',';

        $gas = Auth::user()->gas;
        $users = [];
        $errors = [];

        /*
            TODO: aggiornare questo per adattarlo a UsersService
        */

        foreach($reader->getRecords() as $line) {
            try {
                $new_user = false;
                $login = $line[$login_index];

                $u = User::where('username', '=', $login)->orderBy('id', 'desc')->first();
                if (is_null($u)) {
                    $u = new User();
                    $u->gas_id = $gas->id;
                    $u->username = $login;
                    $u->password = Hash::make($login);
                    $u->member_since = date('Y-m-d');
                    $new_user = true;
                }

                $contacts = [
                    'contact_id' => [],
                    'contact_type' => [],
                    'contact_value' => []
                ];
                $credit = null;
                $address = [];

                foreach ($columns as $index => $field) {
                    $value = (string)$line[$index];

                    if ($field == 'none') {
                        continue;
                    }
                    else if ($field == 'phone' || $field == 'email' || $field == 'mobile') {
                        $contacts['contact_id'][] = '';
                        $contacts['contact_type'][] = $field;
                        $contacts['contact_value'][] = $value;
                        continue;
                    }
                    else if ($field == 'birthday' || $field == 'member_since' || $field == 'last_login') {
                        $u->$field = date('Y-m-d', strtotime($value));
                    }
                    else if ($field == 'credit') {
                        if (!empty($line[$index]) && $line[$index] != 0) {
                            $credit = str_replace(',', '.', $value);
                        }
                    }
                    else if ($field == 'ceased') {
                        if (strtolower($value) == 'true' || strtolower($value) == 'vero' || $value == '1')
                            $u->deleted_at = date('Y-m-d');
                    }
                    else if ($field == 'address_street') {
                        $address[0] = $value;
                    }
                    else if ($field == 'address_zip') {
                        $address[1] = $value;
                    }
                    else if ($field == 'address_city') {
                        $address[2] = $value;
                    }
                    else {
                        $u->$field = $value;
                    }
                }

                $u->save();
                $users[] = $u;

                if (!empty($address)) {
                    $contacts['contact_id'][] = '';
                    $contacts['contact_type'][] = 'address';
                    $contacts['contact_value'][] = join(',', $address);;
                }

                $u->updateContacts($contacts);

                if ($credit != null) {
                    $u->alterBalance($credit, defaultCurrency());
                }

                if ($new_user) {
                    $u->initialWelcome();
                }
            }
            catch (\Exception $e) {
                $errors[] = implode($target_separator, $line).'<br/>'.$e->getMessage();
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
