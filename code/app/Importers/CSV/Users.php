<?php

namespace App\Importers\CSV;

use Auth;
use App;
use DB;
use Hash;

use Illuminate\Support\Str;

use App\User;
use App\Contact;

class Users extends CSVImporter
{
	private function essentialFields(&$ret)
	{
		$ret['firstname'] = (object) [
			'label' => _i('Nome'),
		];

		$ret['lastname'] = (object) [
			'label' => _i('Cognome'),
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
		foreach(Contact::types() as $identifier => $label) {
			if ($identifier == 'address') {
				$ret['address_0'] = (object) [
	                'label' => _i('Indirizzo (Via)'),
	            ];

	            $ret['address_1'] = (object) [
	                'label' => _i('Indirizzo (CAP)'),
	            ];

	            $ret['address_2'] = (object) [
	                'label' => _i('Indirizzo (Città)'),
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
		$ret['birthday'] = (object) [
			'label' => _i('Data di Nascita'),
			'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
		];

		$ret['taxcode'] = (object) [
			'label' => _i('Codice Fiscale'),
		];

		$ret['member_since'] = (object) [
			'label' => _i('Membro da'),
			'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
		];

		$ret['last_login'] = (object) [
			'label' => _i('Ultimo Accesso'),
			'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
		];

		$ret['ceased'] = (object) [
			'label' => _i('Cessato'),
			'explain' => _i('Indicare "true" o "false"')
		];

		$ret['credit'] = (object) [
			'label' => _i('Credito Attuale'),
			'explain' => _i('Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!')
		];
	}

    protected function fields()
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
        if (!empty($value)) {
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

        list($reader, $columns) = $this->initRead($request);
        list($login_index) = $this->getColumnsIndex($columns, ['username']);

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

                $u = $this->retrieveUser($login, $gas);
				$new_user = $u->exists == false;

                $contacts = [
                    'contact_id' => [],
                    'contact_type' => [],
                    'contact_value' => []
                ];

                $credit = null;
                $password_defined = false;
                $address = [];

                foreach ($columns as $index => $field) {
                    $value = (string)$line[$index];

                    if ($field == 'none') {
                        continue;
                    }
                    else if ($field == 'phone' || $field == 'email' || $field == 'mobile') {
                        $this->fillContact($contacts, $field, $value);
                        continue;
                    }
                    else if ($field == 'password') {
                        $u->password = Hash::make($value);
                        $password_defined = true;
                    }
                    else if ($field == 'birthday' || $field == 'member_since' || $field == 'last_login') {
                        $u->$field = date('Y-m-d', strtotime($value));
                    }
                    else if ($field == 'credit') {
                        if (!empty($line[$index]) && $line[$index] != 0) {
                            $credit = guessDecimal($value);
                        }
                    }
                    else if ($field == 'ceased') {
                        if (strtolower($value) == 'true' || strtolower($value) == 'vero' || $value == '1') {
                            $u->deleted_at = date('Y-m-d');
                        }
                    }
                    else if (Str::startsWith($field, 'address_')) {
                        $address[(int) Str::after($value, 'address_')] = $value;
                    }
                    else {
                        $u->$field = $value;
                    }
                }

                $u->save();
                $users[] = $u;

                $this->fillContact($contacts, 'address', join(',', $address));
                $u->updateContacts($contacts);

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
