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
            'label' => __('user.firstname'),
            'mandatory' => true,
        ];

        $ret['lastname'] = (object) [
            'label' => __('user.lastname'),
            'mandatory' => true,
        ];

        $ret['username'] = (object) [
            'label' => __('auth.username'),
            'mandatory' => true,
        ];

        $ret['password'] = (object) [
            'label' => __('auth.password'),
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
                    'label' => __('user.address_part.street'),
                ];

                $ret['address_1'] = (object) [
                    'label' => __('user.address_part.city'),
                ];

                $ret['address_2'] = (object) [
                    'label' => __('user.address_part.zip'),
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
            'label' => __('user.birthplace'),
        ];

        $ret['birthday'] = (object) [
            'label' => __('user.birthdate'),
            'explain' => __('generic.help.preferred_date_format', ['now' => date('Y-m-d')]),
        ];

        $ret['taxcode'] = (object) [
            'label' => __('user.taxcode'),
        ];

        $ret['member_since'] = (object) [
            'label' => __('user.member_since'),
            'explain' => __('generic.help.preferred_date_format', ['now' => date('Y-m-d')]),
        ];

        $ret['card_number'] = (object) [
            'label' => __('user.card_number'),
        ];

        $ret['last_login'] = (object) [
            'label' => __('user.last_login'),
            'explain' => __('generic.help.preferred_date_format', ['now' => date('Y-m-d')]),
        ];

        $groups = Group::where('context', 'user')->orderBy('name', 'asc')->get();
        foreach ($groups as $group) {
            $ret['group_' . $group->id] = (object) [
                'label' => __('user.formatted_aggregation', ['name' => $group->name]),
                'explain' => __('export.help.importing.user.aggregation'),
            ];
        }

        $ret['ceased'] = (object) [
            'label' => __('user.statuses.deleted'),
            'explain' => __('export.help.importing.user.deleted'),
        ];

        $ret['credit'] = (object) [
            'label' => __('movements.current_credit'),
            'explain' => __('user.help.importing.user.balance'),
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
                __('export.help.importing.user.instruction'),
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
                $new_user = !$u->exists;

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

                            if ($new_user === false) {
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

                if ($new_user && $password_defined === false) {
                    $u->initialWelcome();
                }
            }
            catch (\Exception $e) {
                $errors[] = implode(',', $line).'<br/>'.$e->getMessage();
            }
        }

        DB::commit();

        return [
            'title' => __('imports.imported_users'),
            'objects' => $users,
            'errors' => $errors,
        ];
    }
}
