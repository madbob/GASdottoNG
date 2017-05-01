<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Theme;
use Auth;
use CsvReader;
use App\User;
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
                                $u->gas_id = $creator->gas->id;
                                $u->username = $login;
                            }

                            foreach ($columns as $index => $field) {
                                if ($field == 'none') {
                                    continue;
                                } else {
                                    $value = $line[$index];
                                }

                                $u->$field = $value;
                            }

                            $u->save();
                            $users[] = $u;
                        } catch (\Exception $e) {
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
}
