<?php

namespace App\Importers\CSV;

use App;
use DB;

use App\User;
use App\Supplier;
use App\Movement;
use App\MovementType;

class Movements extends CSVImporter
{
    protected function fields()
    {
        return [
            'date' => (object) [
                'label' => _i('Data'),
                'explain' => _i('Preferibilmente in formato YYYY-MM-DD (e.g. %s)', [date('Y-m-d')])
            ],
            'amount' => (object) [
                'label' => _i('Valore'),
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
                    elseif ($field == 'supplier') {
                        $field = 'target_id';

                        $name = trim($line[$index]);
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
                    elseif ($field == 'amount') {
                        $value = guessDecimal($value);
                    }
                    else {
                        $value = $line[$index];
                    }

                    $m->$field = $value;
                }

                if ($save_me)
                    $movements[] = $m;
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

    public function run($request)
    {
        $imports = $request->input('import', []);
        $dates = $request->input('date', []);
        $senders = $request->input('sender_id', []);
        $targets = $request->input('target_id', []);
        $types = $request->input('mtype', []);
        $methods = $request->input('method', []);
        $amounts = $request->input('amount', []);

        $errors = [];
        $movements = [];
        $current_gas = $request->user()->gas;

        DB::beginTransaction();

        foreach($imports as $index) {
            App::make('LogHarvester')->reset();

            try {
                $m = new Movement();
                $m->date = $dates[$index];
                $m->type = $types[$index];
                $m->amount = $amounts[$index];
                $m->method = $methods[$index];

                $t = MovementType::find($m->type);

                $fields = ['sender', 'target'];

                foreach($fields as $f) {
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
                            $m->$id_field = $current_gas->id;
                            $m->$type_field = 'App\Gas';
                            break;
                    }
                }

                $m->save();

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
}
