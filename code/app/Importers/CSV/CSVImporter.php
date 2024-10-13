<?php

namespace App\Importers\CSV;

use Log;

use Illuminate\Support\Str;
use League\Csv\Reader;

use App\Exceptions\MissingFieldException;

abstract class CSVImporter
{
    public function extraInformations()
    {
        return null;
    }

    /*
        Come parametro di questa funzione si usa il nome della sotto-classe di
        CSVImporter che si intende usare, scritto tutto minuscolo
    */
    public static function getImporter($type)
    {
        $classname = 'App\\Importers\\CSV\\' . ucwords($type);
        $ret = new $classname;
        return $ret;
    }

    private function guessCsvFileSeparator($path)
    {
        $contents = fopen($path, 'r');
        if ($contents === false) {
            return null;
        }

        $separators = [',', ';', "\t"];
        $lenghts = [0, 0, 0];

        foreach($separators as $sep_index => $sep) {
            $row = fgetcsv($contents, 0, $sep);
            $lenghts[$sep_index] = count($row);
            rewind($contents);
        }

        // @phpstan-ignore-next-line
        $target_separator = $separators[array_search(max($lenghts), $lenghts)] ?? null;
        // @phpstan-ignore-next-line
        if (is_null($target_separator)) {
            throw new \Exception(_i('Impossibile interpretare il file'), 1);
        }

        return $target_separator;
    }

    /*
        Se la sotto-classe specifica un attributo "sorted_fields" tra i
        parametri, pre-popolo l'array dei campi selezionati in fase di revisione
        del CSV. Questo viene usato, ad esempio, dall'importer "Products" per
        inizializzare l'importazione dei prodotti di un certo fornitore usando
        sempre le stesse colonne (anziché doverle riassegnare a mano ogni volta)
    */
    private function retrievePreSelectedFields($parameters)
    {
        $selected = [];

        if (isset($parameters['sorted_fields']) && empty($parameters['sorted_fields']) == false) {
            $sorted = $parameters['sorted_fields'];
            $fields = $this->fields();

            foreach($parameters['columns'] as $index => $c) {
                if (isset($sorted[$index])) {
                    $selected[] = (object) [
                        'label' => $fields[$sorted[$index]]->label,
                        'name' => $sorted[$index],
                    ];
                }
                else {
                    $selected[] = (object) [
                        'label' => _i('[Ignora]'),
                        'name' => 'none',
                    ];
                }
            }
        }
        else {
            foreach($parameters['columns'] as $c) {
                $selected[] = (object) [
                    'label' => _i('[Ignora]'),
                    'name' => 'none',
                ];
            }
        }

        return $selected;
    }

    protected function storeUploadedFile($request, $parameters)
    {
        try {
            $f = $request->file('file', null);
            if (is_null($f) || $f->isValid() == false) {
                throw new \Exception(_i('File non caricato correttamente, possibili problemi con la dimensione'), 1);
            }

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath . '/' . $filename;

            $target_separator = $this->guessCsvFileSeparator($path);
            $reader = Reader::createFromPath($path, 'r');
            $reader->setDelimiter($target_separator);

            $parameters['path'] = $path;

            $sample_line = '';

            foreach($reader->getRecords() as $line) {
                $sample_line = $line;
                break;
            }

            $parameters['columns'] = $sample_line;
            $parameters['selected'] = $this->retrievePreSelectedFields($parameters);
            return $parameters;
        }
        catch (\Exception $e) {
            Log::error('Unable to load file to import: ' . $e->getMessage());
            throw new \Exception(_i('Errore nel salvataggio del file'), 1);
        }
    }

    protected function getColumnsIndex($columns, $search)
    {
        $ret = [];

        foreach($search as $s) {
            $index = array_search($s, $columns);

            if ($index === false) {
                $ret[] = -1;
            }
            else {
                $ret[] = $index;
            }
        }

        return $ret;
    }

    private function mandatoryFields()
    {
        $ret = [];

        foreach($this->fields() as $key => $meta) {
            $mandatory = $meta->mandatory ?? false;
            if ($mandatory) {
                $ret[] = $key;
            }
        }

        return $ret;
    }

    protected function initRead($request)
    {
        $path = $request->input('path');
        $columns = $request->input('column');

        $testable = $this->mandatoryFields();
        $tested = $this->getColumnsIndex($columns, $testable);

        foreach($tested as $t) {
            if ($t == -1) {
                throw new MissingFieldException(1);
            }
        }

        $target_separator = $this->guessCsvFileSeparator($path);
        $reader = Reader::createFromPath($path, 'r');
        $reader->setDelimiter($target_separator);

        return [$reader, $columns];
    }

    protected function mapNewElements($value, &$cached, $createNew)
    {
        if (Str::startsWith($value, 'new:')) {
            $name = Str::after($value, 'new:');
            if (empty($name) == false) {
                if (isset($cached[$name]) == false) {
                    $obj = $createNew($name);
                    $cached[$name] = $obj->id;
                }

                return $cached[$name];
            }
            else {
                return $name;
            }
        }

        return $value;
    }

    public function finalTemplate()
    {
        return 'import.csvimportfinal';
    }

    public abstract function fields();
    public abstract function testAccess($request);
    public abstract function guess($request);
    public abstract function select($request);
    public abstract function formatSelect($parameters);
    public abstract function run($request);
}
