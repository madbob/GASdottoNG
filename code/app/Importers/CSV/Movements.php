<?php

namespace App\Importers\CSV;

use App;
use DB;

use App\User;
use App\Supplier;
use App\Movement;
use App\MovementType;
use App\Currency;

class Movements extends CSVImporter
{
    public function fields()
    {
        $ret = [
            'date' => (object) [
                'label' => _i('Data'),
                'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
            ],
            'amount' => (object) [
                'label' => _i('Valore'),
            ],
            'identifier' => (object) [
                'label' => _i('Identificativo'),
            ],
            'notes' => (object) [
                'label' => _i('Note'),
            ],
            'user' => (object) [
                'label' => _i('Utente'),
                'explain' => _i('Username o indirizzo e-mail')
            ],
            'supplier' => (object) [
                'label' => _i('Fornitore'),
                'explain' => _i('Nome o partita IVA')
            ],
        ];

        $currencies = Currency::enabled();
        if ($currencies->count() > 1) {
            $ret['currency'] = (object) [
                'label' => _i('Valuta'),
                'explain' => _i('Una delle valute gestite dal sistema. Se non specificato, verrà selezionata quella di default(%s). Valori ammessi: %s', [defaultCurrency()->symbol, $currencies->pluck('symbol')->join(' / ')]),
            ];
        }

        return $ret;
    }

    public function testAccess($request)
    {
        $user = $request->user();
        return $user->can('movements.admin', $user->gas);
    }

    public function guess($request)
    {
        return $this->storeUploadedFile($request, [
            'type' => 'movements',
            'next_step' => 'select',
            'extra_description' => [
                _i('Gli utenti sono identificati per username o indirizzo mail (che deve essere univoco!).')
            ],
            'sorting_fields' => $this->fields(),
        ]);
    }

    public function select($request)
    {
        list($reader, $columns) = $this->initRead($request);
        $target_separator = ',';

        $movements = [];
        $errors = [];

        $cached_currencies = [];
        $default_currency = defaultCurrency();
        $cached_currencies[$default_currency->symbol] = $default_currency;

        foreach($reader->getRecords() as $line) {
            try {
                /*
                    In questa fase, genero dei Movement
                    temporanei al solo scopo di popolare la
                    vista di selezione.
                    Non salvare gli oggetti qui creati!!!
                */
                $m = new Movement();
                $m->method = 'bank';
                $m->currency_id = $default_currency->id;
                $save_me = true;

                foreach ($columns as $index => $field) {
                    if ($field == 'none') {
                        continue;
                    }
                    elseif ($field == 'date') {
                        $value = date('Y-m-d', readDate($line[$index]));
                    }
                    elseif ($field == 'user') {
                        $field = 'sender_id';

                        $name = trim($line[$index]);
                        if (filled($name)) {
                            $user = User::where('username', $name)->first();

                            if (is_null($user)) {
                                $user = User::whereHas('contacts', function($query) use ($name) {
                                    $query->where('value', $name);
                                })->first();

                                if (is_null($user)) {
                                    $save_me = false;
                                    $errors[] = implode($target_separator, $line) . '<br/>' . _i('Utente non trovato: %s', $name);
                                    continue;
                                }
                            }

                            $value = $user->id;
                        }
                        else {
                            continue;
                        }
                    }
                    elseif ($field == 'supplier') {
                        $field = 'target_id';

                        $name = trim($line[$index]);
                        if (filled($name)) {
                            $supplier = Supplier::where('name', $name)->first();

                            if (is_null($supplier)) {
                                $supplier = Supplier::where('vat', $name)->first();

                                if (is_null($supplier)) {
                                    $save_me = false;
                                    $errors[] = implode($target_separator, $line) . '<br/>' . _i('Fornitore non trovato: %s', $name);
                                    continue;
                                }
                            }

                            $value = $supplier->id;
                        }
                        else {
                            continue;
                        }
                    }
                    elseif ($field == 'currency') {
                        $field = 'currency_id';
                        $value = $line[$index];

                        if (!isset($cached_currencies[$value])) {
                            $cached_currencies[$value] = Currency::where('symbol', $value)->where('enabled', true)->first();
                        }

                        if ($cached_currencies[$value]) {
                            $value = $cached_currencies[$value]->id;
                        }
                        else {
                            $save_me = false;
                            $errors[] = implode($target_separator, $line) . '<br/>' . _i('Valuta non trovata: %s', $value);
                            continue;
                        }
                    }
                    elseif ($field == 'amount') {
                        $value = $line[$index];
                        $value = guessDecimal($value);
                    }
                    else {
                        $value = $line[$index];
                    }

                    $m->$field = $value;
                }

                if ($save_me) {
                    $movements[] = $m;
                }
            }
            catch (\Exception $e) {
                $errors[] = implode($target_separator, $line) . '<br/>' . $e->getMessage();
            }
        }

        return ['movements' => $movements, 'errors' => $errors];
    }

    public function formatSelect($parameters)
    {
        return view('import.csvmovementsselect', $parameters);
    }

    private function assignPeers($m, $senders, $targets, $index)
    {
        $t = MovementType::find($m->type);

        foreach(['sender', 'target'] as $f) {
            $id_field = $f . '_id';
            $type_field = $f . '_type';

            switch($t->$type_field) {
                case 'App\User':
                    if ($senders[$index] !== '0') {
                        $m->$id_field = $senders[$index];
                        $m->$type_field = 'App\User';
                    }
                    break;

                case 'App\Supplier':
                    if ($targets[$index] !== '0') {
                        $m->$id_field = $targets[$index];
                        $m->$type_field = 'App\Supplier';
                    }
                    break;

                case 'App\Gas':
                    $current_gas = request()->user()->gas;
                    $m->$id_field = $current_gas->id;
                    $m->$type_field = 'App\Gas';
                    break;
            }
        }

        return $m;
    }

    public function run($request)
    {
        $imports = $request->input('import', []);
        $dates = $request->input('date', []);
        $senders = $request->input('sender_id', []);
        $targets = $request->input('target_id', []);
        $notes = $request->input('notes', []);
        $types = $request->input('mtype', []);
        $methods = $request->input('method', []);
        $amounts = $request->input('amount', []);
        $identifiers = $request->input('identifier', []);
        $currencies = $request->input('currency_id', []);

        $errors = [];
        $movements = [];

        DB::beginTransaction();

        foreach($imports as $index) {
            App::make('LogHarvester')->reset();

            try {
                $m = new Movement();
                $m->date = $dates[$index];
                $m->type = $types[$index];
                $m->amount = $amounts[$index];
                $m->identifier = $identifiers[$index];
                $m->method = $methods[$index];
                $m->currency_id = $currencies[$index];
                $m->notes = $notes[$index];
                $m = $this->assignPeers($m, $senders, $targets, $index);
                $m->save();

                /*
                    Ricordarsi sempre che la funzione di salvataggio dei
                    Movements non necessariamente salva per davvero il
                    movimento, intervenendo in questa fase le callback di
                    controllo· Qui è lecito fare un controllo
                */
                if ($m->exists) {
                    $movements[] = $m;
                }
                else {
                    $errors[] = $index . '<br/>' . App::make('LogHarvester')->last();
                }
            }
            catch (\Exception $e) {
                $errors[] = $index . '<br/>' . $e->getMessage();
            }
        }

        DB::commit();

        return [
            'title' => _i('Movimenti importati'),
            'objects' => $movements,
            'errors' => $errors
        ];
    }

    public function finalTemplate()
    {
        return 'import.csvimportmovementsfinal';
    }
}
