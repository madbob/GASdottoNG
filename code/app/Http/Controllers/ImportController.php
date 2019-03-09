<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Console\Output\BufferedOutput;

use DB;
use Auth;
use Log;
use App;
use Hash;
use CsvReader;
use ezcArchive;
use Artisan;

use App\User;
use App\Contact;
use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;
use App\VatRate;
use App\Movement;
use App\MovementType;

class ImportController extends Controller
{
    private function guessCsvFileSeparator($path)
    {
        $contents = fopen($path, 'r');
        if (is_null($contents)) {
            return null;
        }

        $separators = [',', ';', "\t"];
        $target_separator = null;

        while (!feof($contents) && is_null($target_separator)) {
            $char = fgetc($contents);
            foreach ($separators as $del) {
                if ($char == $del) {
                    $target_separator = $del;
                    break;
                }
            }
        }

        fclose($contents);

        return $target_separator;
    }

    private function storeUploadedFile(Request $request, $parameters)
    {
        try {
            $f = $request->file('file', null);
            if (is_null($f) || $f->isValid() == false) {
                return $this->errorResponse(_i('File non caricato correttamente, possibili problemi con la dimensione'));
            }

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath.'/'.$filename;

            $target_separator = $this->guessCsvFileSeparator($path);
            if (is_null($target_separator)) {
                return $this->errorResponse(_i('Impossibile interpretare il file'));
            }

            $reader = CsvReader::open($path, $target_separator);
            $sample_line = $reader->readLine();

            $parameters['path'] = $path;
            $parameters['columns'] = $sample_line;

            return view('import.csvsortcolumns', $parameters);

        } catch (\Exception $e) {
            return $this->errorResponse(_i('Errore nel salvataggio del file'));
        }
    }

    public function getLegacy()
    {
        return view('import.legacy-pre');
    }

    public function postLegacy(Request $request)
    {
        $old_path = $request->input('old_path');
        $config = sprintf('%s/server/config.php', $old_path);

        if (file_exists($config) == false) {
            return view('import.legacy-pre', ['error' => _i('Il file di configurazione non è stato trovato in %s', $config)]);
        }
        else {
            require_once($config);

            $output = new BufferedOutput();

            Artisan::call('import:legacy', [
                'old_path' => $old_path,
                'old_driver' => $dbdriver,
                'old_host' => isset($dbhost) ? $dbhost : 'localhost',
                'old_username' => $dbuser,
                'old_password' => $dbpassword,
                'old_database' => $dbname
            ], $output);

            return view('import.legacy-post', ['output' => $output]);
        }
    }

    public function postCsv(Request $request)
    {
        $type = $request->input('type');
        $step = $request->input('step', 'guess');

        if ($type == 'products') {
            $supplier_id = $request->input('supplier_id');
            $s = Supplier::findOrFail($supplier_id);
            if ($request->user()->can('supplier.modify', $s) == false) {
                return $this->errorResponse(_i('Non autorizzato'));
            }

            /*
                TODO: indovinare se il file ha una riga di intestazione
            */

            switch ($step) {
                case 'guess':
                    return $this->storeUploadedFile($request, [
                        'type' => 'products',
                        'next_step' => 'select',
                        'extra_fields' => [
                            'supplier_id' => $s->id
                        ],
                        'extra_description' => [
                            _i('Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.')
                        ],
                        'sorting_fields' => [
                            'name' => (object) [
                                'label' => _i('Nome'),
                                'mandatory' => true
                            ],
                            'description' => (object) [
                                'label' => _i('Descrizione')
                            ],
                            'price' => (object) [
                                'label' => _i('Prezzo Unitario'),
                            ],
                            'price_without_vat' => (object) [
                                'label' => _i('Prezzo Unitario (senza IVA)'),
                                'explain' => _i('Da usare in combinazione con Aliquota IVA')
                            ],
                            'vat' => (object) [
                                'label' => _i('Aliquota IVA'),
                            ],
                            'transport' => (object) [
                                'label' => _i('Prezzo Trasporto'),
                            ],
                            'category' => (object) [
                                'label' => _i('Categoria'),
                            ],
                            'measure' => (object) [
                                'label' => _i('Unità di Misura'),
                            ],
                            'supplier_code' => (object) [
                                'label' => _i('Codice Fornitore'),
                            ],
                            'package_size' => (object) [
                                'label' => _i('Dimensione Confezione'),
                            ],
                            'package_price' => (object) [
                                'label' => _i('Prezzo Confezione'),
                                'explain' => _i('Se specificato, il prezzo unitario viene calcolato come Prezzo Confezione / Dimensione Confezione')
                            ],
                            'min_quantity' => (object) [
                                'label' => _i('Ordine Minimo'),
                            ],
                            'multiple' => (object) [
                                'label' => _i('Ordinabile per Multipli')
                            ],
                        ]
                    ]);
                    break;

                case 'select':
                    $path = $request->input('path');
                    $columns = $request->input('column');

                    $errors = [];
                    $name_index = -1;
                    $supplier_code_index = -1;

                    foreach ($columns as $index => $field) {
                        if ($field == 'name')
                            $name_index = $index;
                        if ($field == 'supplier_code')
                            $supplier_code_index = $index;
                    }

                    if ($name_index == -1) {
                        $errors[] = _i('Colonna obbligatoria non specificata: nome del prodotto');
                    }

                    if (!empty($errors)) {
                        return view('import.csvimportfinal', [
                            'title' => _i('Prodotti importati'),
                            'objects' => [],
                            'errors' => $errors,
                            'extra_closing_attributes' => [
                                'data-reload-target' => '#supplier-list'
                            ]
                        ]);
                    }

                    $target_separator = $this->guessCsvFileSeparator($path);
                    if (is_null($target_separator)) {
                        return $this->errorResponse(_i('Impossibile interpretare il file'));
                    }

                    $products = [];

                    $reader = CsvReader::open($path, $target_separator);
                    while (($line = $reader->readLine()) !== false) {
                        try {
                            $name = $line[$name_index];

                            $p = new Product();
                            $p->name = $name;
                            $p->category_id = 'non-specificato';
                            $p->measure_id = 'non-specificato';

                            if ($supplier_code_index == -1) {
                                $test = $s->products()->where('name', $name)->orderBy('id', 'desc')->first();
                            }
                            else {
                                $test = $s->products()->where('name', $name)->orWhere('supplier_code', $line[$supplier_code_index])->orderBy('id', 'desc')->first();
                            }

                            if (is_null($test) == false) {
                                $p->want_replace = $test->id;
                            }
                            else {
                                $p->want_replace = -1;
                            }

                            $price_without_vat = null;
                            $vat_rate = null;
                            $package_price = null;

                            foreach ($columns as $index => $field) {
                                $value = trim($line[$index]);

                                if ($field == 'none') {
                                    continue;
                                }
                                elseif ($field == 'category') {
                                    $test_category = Category::where('name', $value)->first();
                                    if (is_null($test_category)) {
                                        $field = 'category_name';
                                        $p->category_id = -1;
                                    }
                                    else {
                                        $p->category_id = $test_category->id;
                                    }
                                }
                                elseif ($field == 'measure') {
                                    $test_measure = Measure::where('name', $value)->first();
                                    if (is_null($test_measure)) {
                                        $field = 'measure_name';
                                        $p->measure_id = -1;
                                    }
                                    else {
                                        $p->measure_id = $test_measure->id;
                                    }
                                }
                                elseif ($field == 'price' || $field == 'transport') {
                                    $value = str_replace(',', '.', $value);
                                }
                                elseif ($field == 'price_without_vat') {
                                    $price_without_vat = str_replace(',', '.', $value);
                                    continue;
                                }
                                elseif ($field == 'vat') {
                                    $value = str_replace(',', '.', $value);
                                    $vat_rate = $value = (float) $value;

                                    $test_vat = VatRate::where('percentage', $value)->first();
                                    if (is_null($test_vat)) {
                                        $field = 'vat_rate_name';
                                        $p->vat_rate_id = -1;
                                    }
                                    else {
                                        $p->vat_rate_id = $test_vat->id;
                                    }
                                }
                                elseif ($field == 'package_price') {
                                    $package_price = str_replace(',', '.', $value);
                                    continue;
                                }

                                $p->$field = $value;
                            }

                            if (!empty($package_price) && !empty($p->package_size) && empty($p->price)) {
                                $p->price = $package_price / $p->package_size;
                            }

                            if (!empty($price_without_vat) && !empty($vat_rate)) {
                                $p->price = $price_without_vat + (($price_without_vat * $vat_rate) / 100);
                            }

                            $products[] = $p;
                        }
                        catch (\Exception $e) {
                            $products[] = implode($target_separator, $line).'<br/>'.$e->getMessage();
                        }
                    }

                    return view('import.csvproductsselect', ['products' => $products, 'supplier' => $s, 'errors' => $errors]);
                    break;

                case 'run':
                    DB::beginTransaction();

                    $imports = $request->input('import');
                    $names = $request->input('name');
                    $descriptions = $request->input('description');
                    $prices = $request->input('price');
                    $transports = $request->input('transport');
                    $categories = $request->input('category_id');
                    $measures = $request->input('measure_id');
                    $vat_rates = $request->input('vat_rate_id');
                    $codes = $request->input('supplier_code');
                    $sizes = $request->input('package_size');
                    $mins = $request->input('min_quantity');
                    $multiples = $request->input('multiple');
                    $replaces = $request->input('want_replace');

                    $errors = [];
                    $products = [];
                    $products_ids = [];

                    foreach($imports as $index) {
                        try {
                            if ($replaces[$index] != -1) {
                                $p = Product::find($replaces[$index]);
                            }
                            else {
                                $p = new Product();
                                $p->supplier_id = $s->id;
                            }

                            $p->active = true;

                            $p->name = $names[$index];
                            $p->description = $descriptions[$index];
                            $p->price = $prices[$index];
                            $p->transport = $transports[$index];
                            $p->supplier_code = $codes[$index];
                            $p->package_size = $sizes[$index];
                            $p->min_quantity = $mins[$index];
                            $p->multiple = $multiples[$index];

                            if (starts_with($categories[$index], 'new:')) {
                                $category = new Category();
                                $category->name = str_after($categories[$index], 'new:');
                                $category->save();
                                $categories[$index] = $category->id;
                            }
                            $p->category_id = $categories[$index];

                            if (starts_with($measures[$index], 'new:')) {
                                $measure = new Measure();
                                $measure->name = str_after($measures[$index], 'new:');
                                $measure->save();
                                $measures[$index] = $measure->id;
                            }
                            $p->measure_id = $measures[$index];

                            if (starts_with($vat_rates[$index], 'new:')) {
                                $vat = new VatRate();
                                $vat->percentage = str_after($vat_rates[$index], 'new:');
                                $vat->name = sprintf('%f %', $vat->percentage);
                                $vat->save();
                                $vat_rates[$index] = $vat->id;
                            }

                            if (!empty($vat_rates[$index]))
                                $p->vat_rate_id = $vat_rates[$index];

                            $p->save();
                            $products[] = $p;
                            $products_ids[] = $p->id;
                        }
                        catch (\Exception $e) {
                            $errors[] = $index . '<br/>' . $e->getMessage();
                        }
                    }

                    if ($request->has('reset_list')) {
                        $s->products()->whereNotIn('id', $products_ids)->update(['active' => false]);
                    }

                    DB::commit();

                    return view('import.csvimportfinal', [
                        'title' => _i('Prodotti importati'),
                        'objects' => $products,
                        'errors' => $errors,
                        'extra_closing_attributes' => [
                            'data-reload-target' => '#supplier-list'
                        ]
                    ]);

                    break;
            }
        }
        else if ($type == 'users') {
            switch ($step) {
                case 'guess':
                    return $this->storeUploadedFile($request, [
                        'type' => 'users',
                        'sorting_fields' => [
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
                                'label' => _i('Credito Attuale')
                            ]
                        ]
                    ]);
                    break;

                case 'run':
                    DB::beginTransaction();

                    $path = $request->input('path');
                    $columns = $request->input('column');

                    $login_index = -1;

                    foreach ($columns as $index => $field) {
                        if ($field == 'username') {
                            $login_index = $index;
                            break;
                        }
                    }

                    if ($login_index == -1) {
                        return $this->errorResponse(_i('Colonna obbligatoria non specificata'));
                    }

                    $target_separator = $this->guessCsvFileSeparator($path);
                    if (is_null($target_separator)) {
                        return $this->errorResponse(_i('Impossibile interpretare il file'));
                    }

                    $creator = Auth::user();
                    $gas = $creator->gas;
                    $users = [];
                    $errors = [];

                    /*
                        TODO: aggiornare questo per adattarlo a UsersService
                    */

                    $reader = CsvReader::open($path, $target_separator);
                    while (($line = $reader->readLine()) !== false) {
                        try {
                            $login = $line[$login_index];
                            $u = User::where('username', '=', $login)->orderBy('id', 'desc')->first();
                            if (is_null($u)) {
                                $u = new User();
                                $u->gas_id = $gas->id;
                                $u->username = $login;
                                $u->password = Hash::make($login);
                                $u->member_since = date('Y-m-d');
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
                                $u->alterBalance($credit);
                            }
                        }
                        catch (\Exception $e) {
                            $errors[] = implode($target_separator, $line).'<br/>'.$e->getMessage();
                        }
                    }

                    DB::commit();

                    return view('import.csvimportfinal', [
                        'title' => _i('Utenti importati'),
                        'objects' => $users,
                        'errors' => $errors,
                    ]);

                    break;
            }
        }
        else if ($type == 'movements') {
            $user = $request->user();
            if ($user->can('movements.admin', $user->gas) == false) {
                return $this->errorResponse(_i('Non autorizzato'));
            }

            switch ($step) {
                case 'guess':
                    return $this->storeUploadedFile($request, [
                        'type' => 'movements',
                        'next_step' => 'select',
                        'extra_description' => [
                            _i('Gli utenti sono identificati per username o indirizzo mail (che deve essere univoco!).')
                        ],
                        'sorting_fields' => [
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
                            ]
                        ]
                    ]);
                    break;

                case 'select':
                    $path = $request->input('path');
                    $columns = $request->input('column');

                    $target_separator = $this->guessCsvFileSeparator($path);
                    if (is_null($target_separator)) {
                        return $this->errorResponse(_i('Impossibile interpretare il file'));
                    }

                    $movements = [];
                    $errors = [];

                    $reader = CsvReader::open($path, $target_separator);
                    while (($line = $reader->readLine()) !== false) {
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
                                    }

                                    if (is_null($user)) {
                                        $save_me = false;
                                        $errors[] = implode($target_separator, $line) . '<br/>' . _i('Utente non trovato: %s', $name);
                                        continue;
                                    }

                                    $value = $user->id;
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

                    return view('import.csvmovementsselect', ['movements' => $movements, 'errors' => $errors]);
                    break;

                case 'run':
                    $imports = $request->input('import', []);
                    $dates = $request->input('date', []);
                    $senders = $request->input('sender_id', []);
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

                            if ($senders[$index] !== '0') {
                                if ($t->sender_type == 'App\User') {
                                    $m->sender_id = $senders[$index];
                                    $m->sender_type = 'App\User';
                                }
                                if ($t->target_type == 'App\User') {
                                    $m->target_id = $senders[$index];
                                    $m->target_type = 'App\User';
                                }
                            }

                            if ($t->sender_type == 'App\Gas') {
                                $m->sender_id = $current_gas->id;
                                $m->sender_type = 'App\Gas';
                            }
                            if ($t->target_type == 'App\Gas') {
                                $m->target_id = $current_gas->id;
                                $m->target_type = 'App\Gas';
                            }

                            $m->save();

                            if ($m->exists)
                                $movements[] = $m;
                            else
                                $errors[] = $index . '<br/>' . App::make('LogHarvester')->last();
                        }
                        catch (\Exception $e) {
                            $errors[] = $index . '<br/>' . $e->getMessage();
                        }
                    }

                    DB::commit();

                    return view('import.csvimportfinal', [
                        'title' => _i('Movimenti importati'),
                        'objects' => $movements,
                        'errors' => $errors
                    ]);

                    break;
            }
        }

        return $this->errorResponse(_i('Comando %s/%s non valido', $type, $step));
    }

    public function getGdxp(Request $request)
    {
        $classname = $request->input('classname');
        $id = $request->input('id');
        $obj = $classname::findOrFail($id);
        $xml = $obj->exportXML();

        $working_dir = sys_get_temp_dir();
        chdir($working_dir);
        $filename = md5($xml);
        file_put_contents($filename, $xml);

        $archivepath = sprintf('%s/%s.gdxp', $working_dir, str_replace('/', '_', $obj->printableName()));
        $archive = ezcArchive::open('compress.zlib://' . $archivepath, ezcArchive::TAR_USTAR);
        $archive->append([$filename], '');
        unlink($filename);

        return response()->download($archivepath)->deleteFileAfterSend(true);
    }

    private function readGdxpFile($path, $execute, $supplier_replace)
    {
        $working_dir = sys_get_temp_dir();

        $data = [];
        $archive = ezcArchive::open('compress.zlib://' . $path);
        while($archive->valid()) {
            $entry = $archive->current();
            $archive->extractCurrent($working_dir);
            $filepath = sprintf('%s/%s', $working_dir, $entry->getPath());
            $contents = file_get_contents($filepath);
            $contents = simplexml_load_string($contents);

            foreach($contents->children() as $c) {
                if ($execute)
                    $data[] = Supplier::importXML($c, $supplier_replace);
                else
                    $data[] = Supplier::readXML($c);
            }

            unlink($filepath);
            $archive->next();
        }

        return $data;
    }

    public function postGdxp(Request $request)
    {
        try {
            $archivepath = '';
            $working_dir = sys_get_temp_dir();
            $step = $request->input('step', 'read');

            if ($step == 'read') {
                $file = $request->file('file');
                $filename = basename(tempnam($working_dir, 'import_gdxp_'));
                $file->move($working_dir, $filename);
                $archivepath = sprintf('%s/%s', $working_dir, $filename);

                $data = $this->readGdxpFile($archivepath, false, null);
                return view('import.gdxpsummary', ['data' => $data, 'path' => $archivepath]);
            }
            else if ($step == 'run') {
                DB::beginTransaction();

                $archivepath = $request->input('path');
                if ($request->input('supplier_source') == 'new')
                    $data = $this->readGdxpFile($archivepath, true, null);
                else
                    $data = $this->readGdxpFile($archivepath, true, $request->input('supplier_update'));

                unlink($archivepath);
                DB::commit();

                return view('import.gdxpfinal', ['data' => $data]);
            }
        }
        catch(\Exception $e) {
            Log::error(_i('Errore importando file GDXP: %s', $e->getMessage()));
            return view('import.gdxperror');
        }
    }
}
