<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Console\Output\BufferedOutput;

use DB;
use Theme;
use Auth;
use Log;
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

class ImportController extends Controller
{
    private function guessCsvFileSeparator($path)
    {
        $contents = fopen($path, 'r');
        if ($contents === null) {
            return null;
        }

        $separators = [',', ';', "\t"];
        $target_separator = null;

        while (!feof($contents) && $target_separator == null) {
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

    private function storeUploadedFile(Request $request, $response, $parameters)
    {
        try {
            $f = $request->file('file', null);
            if ($f == null || $f->isValid() == false) {
                return $this->errorResponse('File non caricato correttamente, possibili problemi con la dimensione');
            }

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath.'/'.$filename;

            $target_separator = $this->guessCsvFileSeparator($path);
            if ($target_separator == null) {
                return $this->errorResponse('Impossibile interpretare il file');
            }

            $reader = CsvReader::open($path, $target_separator);
            $sample_line = $reader->readLine();

            $parameters['path'] = $path;
            $parameters['columns'] = $sample_line;

            return Theme::view($response, $parameters);

        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel salvataggio del file');
        }
    }

    public function getLegacy()
    {
        return Theme::view('import.legacy-pre');
    }

    public function postLegacy(Request $request)
    {
        $old_path = $request->input('old_path');
        $config = sprintf('%s/server/config.php', $old_path);

        if (file_exists($config) == false) {
            return Theme::view('import.legacy-pre', ['error' => 'Il file di configurazione non è stato trovato in ' . $config]);
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

            return Theme::view('import.legacy-post', ['output' => $output]);
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
                return $this->errorResponse('Non autorizzato');
            }

            /*
                TODO: indovinare se il file ha una riga di intestazione
            */

            switch ($step) {
                case 'guess':
                    return $this->storeUploadedFile($request, 'import.csvproductssortcolumns', ['supplier' => $s]);
                    break;

                case 'run':
                    DB::beginTransaction();

                    $path = $request->input('path');
                    $columns = $request->input('column');

                    $errors = [];
                    $name_index = -1;
                    $category_index = -1;
                    $measure_index = -1;

                    foreach ($columns as $index => $field) {
                        if ($field == 'name')
                            $name_index = $index;
                        else if ($field == 'category')
                            $category_index = $index;
                        else if ($field == 'measure')
                            $measure_index = $index;
                    }

                    if ($name_index == -1) {
                        $errors[] = 'Colonna obbligatoria non specificata: nome del prodotto';
                    }
                    if ($category_index == -1) {
                        $errors[] = 'Colonna obbligatoria non specificata: categoria';
                    }
                    if ($measure_index == -1) {
                        $errors[] = 'Colonna obbligatoria non specificata: unità di misura';
                    }

                    if (!empty($errors))
                        return Theme::view('import.csvproductsfinal', ['supplier' => $s, 'products' => [], 'errors' => $errors]);

                    $target_separator = $this->guessCsvFileSeparator($path);
                    if ($target_separator == null) {
                        return $this->errorResponse('Impossibile interpretare il file');
                    }

                    $products = [];

                    $reader = CsvReader::open($path, $target_separator);
                    while (($line = $reader->readLine()) !== false) {
                        try {
                            $name = $line[$name_index];
                            $p = $s->products()->where('name', '=', $name)->orderBy('id', 'desc')->first();
                            if ($p == null) {
                                $p = new Product();
                                $p->name = $name;
                                $p->supplier_id = $s->id;
                            }

                            $p->active = true;

                            foreach ($columns as $index => $field) {
                                if ($field == 'none') {
                                    continue;
                                } elseif ($field == 'category') {
                                    $name = $line[$index];
                                    $category = Category::where('name', '=', $name)->first();
                                    if ($category == null) {
                                        $category = new Category();
                                        $category->name = $name;
                                        $category->save();
                                    }

                                    $field = 'category_id';
                                    $value = $category->id;
                                } elseif ($field == 'measure') {
                                    $name = $line[$index];
                                    $measure = Measure::where('name', '=', $name)->first();
                                    if ($measure == null) {
                                        $measure = new Measure();
                                        $measure->name = $name;
                                        $measure->save();
                                    }

                                    $field = 'measure_id';
                                    $value = $measure->id;
                                } elseif ($field == 'price' || $field == 'transport') {
                                    $value = str_replace(',', '.', $line[$index]);
                                } else {
                                    $value = $line[$index];
                                }

                                $p->$field = $value;
                            }

                            $p->save();
                            $products[] = $p;
                        } catch (\Exception $e) {
                            $errors[] = implode($target_separator, $line).'<br/>'.$e->getMessage();
                        }
                    }

                    DB::commit();

                    return Theme::view('import.csvproductsfinal', ['supplier' => $s, 'products' => $products, 'errors' => $errors]);
                    break;
            }
        }
        else if ($type == 'users') {
            switch ($step) {
                case 'guess':
                    return $this->storeUploadedFile($request, 'import.csvuserssortcolumns', []);
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
                        return $this->errorResponse('Colonna obbligatoria non specificata');
                    }

                    $target_separator = $this->guessCsvFileSeparator($path);
                    if ($target_separator == null) {
                        return $this->errorResponse('Impossibile interpretare il file');
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
                            if ($u == null) {
                                $u = new User();
                                $u->gas_id = $gas->id;
                                $u->username = $login;
                                $u->password = Hash::make($login);
                            }

                            $contacts = [];
                            $credit = null;

                            foreach ($columns as $index => $field) {
                                if ($field == 'none') {
                                    continue;
                                }
                                else if ($field == 'phone' || $field == 'email') {
                                    $c = new Contact();
                                    $c->type = $field;
                                    $c->value = $line[$index];
                                    $contacts[] = $c;
                                    continue;
                                }
                                else if ($field == 'member_since') {
                                    $u->$field = date('Y-m-d', strtotime($line[$index]));
                                }
                                else if ($field == 'credit') {
                                    if (!empty($line[$index]) && $line[$index] != 0) {
                                        $credit = str_replace(',', '.', $line[$index]);
                                    }
                                }
                                else {
                                    $u->$field = $line[$index];
                                }
                            }

                            $u->save();
                            $users[] = $u;

                            if (!empty($contacts)) {
                                foreach($contacts as $c) {
                                    $c->target_id = $u->id;
                                    $c->target_type = get_class($u);
                                    $c->save();
                                }
                            }

                            if ($credit != null) {
                                $u->alterBalance($credit);
                            }
                        }
                        catch (\Exception $e) {
                            $errors[] = implode($target_separator, $line).'<br/>'.$e->getMessage();
                        }
                    }

                    DB::commit();

                    return Theme::view('import.csvusersfinal', ['users' => $users, 'errors' => $errors]);
                    break;
            }
        }

        return $this->errorResponse('Comando non valido');
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
        $archive->append($filename, '');
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
                return Theme::view('import.gdxpsummary', ['data' => $data, 'path' => $archivepath]);
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

                return Theme::view('import.gdxpfinal', ['data' => $data]);
            }
        }
        catch(\Exception $e) {
            Log::error('Errore importando file GDXP');
            return Theme::view('import.gdxperror');
        }
    }
}
