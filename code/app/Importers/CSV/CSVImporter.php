<?php

namespace App\Importers\CSV;

use Log;

use Illuminate\Support\Str;
use League\Csv\Reader;

use App\Exceptions\MissingFieldException;

abstract class CSVImporter
{
    private $reader;

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
        $ret = new $classname();

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

        foreach ($separators as $sep_index => $sep) {
            $row = fgetcsv($contents, 0, $sep);
            $lenghts[$sep_index] = count($row);
            rewind($contents);
        }

        $target_separator = $separators[array_search(max($lenghts), $lenghts)] ?? null;
        if (is_null($target_separator)) {
            throw new \InvalidArgumentException('Impossibile interpretare il file');
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

        if (isset($parameters['sorted_fields']) && empty($parameters['sorted_fields']) === false) {
            $sorted = $parameters['sorted_fields'];
            $fields = $this->fields();

            foreach ($parameters['columns'] as $index => $c) {
                if (isset($sorted[$index]) && isset($fields[$sorted[$index]])) {
                    $selected[] = (object) [
                        'label' => $fields[$sorted[$index]]->label,
                        'name' => $sorted[$index],
                    ];
                }
                else {
                    $selected[] = (object) [
                        'label' => __('imports.ignore_slot'),
                        'name' => 'none',
                    ];
                }
            }
        }
        else {
            foreach ($parameters['columns'] as $c) {
                $selected[] = (object) [
                    'label' => __('imports.ignore_slot'),
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
            if (is_null($f) || $f->isValid() === false) {
                throw new \InvalidArgumentException('File non caricato correttamente, possibili problemi con la dimensione');
            }

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath . '/' . $filename;

            $target_separator = $this->guessCsvFileSeparator($path);
            $this->reader = Reader::createFromPath($path, 'r');
            $this->reader->setDelimiter($target_separator);

            $parameters['path'] = $path;

            $sample_line = '';

            foreach ($this->reader->getRecords() as $line) {
                $sample_line = $line;
                break;
            }

            $parameters['columns'] = $sample_line;
            $parameters['selected'] = $this->retrievePreSelectedFields($parameters);

            return $parameters;
        }
        catch (\Exception $e) {
            Log::error('Unable to load file to import: ' . $e->getMessage());
            throw new \RuntimeException('Errore nel salvataggio del file');
        }
    }

    protected function getColumnsIndex($columns, $search)
    {
        $ret = [];

        foreach ($search as $s) {
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

        foreach ($this->fields() as $key => $meta) {
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

        foreach ($tested as $t) {
            if ($t == -1) {
                throw new MissingFieldException(1);
            }
        }

        $target_separator = $this->guessCsvFileSeparator($path);
        $this->reader = Reader::createFromPath($path, 'r');
        $this->reader->setDelimiter($target_separator);

        return $columns;
    }

    protected function getRecords()
    {
        $ret = $this->reader->getRecords();
        $ret = iterator_to_array($ret);

        /*
            Faccio il trim() di tutti i valori
        */
        $ret = array_map(fn ($row) => array_map(fn ($v) => trim($v), $row), $ret);

        /*
            Elimino tutte le righe vuote (ovvero: i cui valori sono tutti vuoti)
        */
        $ret = array_filter($ret, function ($row) {
            $test = array_filter($row, fn ($v) => ! empty($v));

            return ! empty($test);
        });

        return $ret;
    }

    protected function mapNewElements($value, &$cached, $createNew)
    {
        if (Str::startsWith($value, 'new:')) {
            $name = Str::after($value, 'new:');
            if (empty($name) === false) {
                if (isset($cached[$name]) === false) {
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

    abstract public function fields();

    abstract public function testAccess($request);

    abstract public function guess($request);

    abstract public function select($request);

    abstract public function formatSelect($parameters);

    abstract public function run($request);
}
