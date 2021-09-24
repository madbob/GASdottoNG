<?php

namespace App\Importers\CSV;

use Log;

use League\Csv\Reader;

use App\Exceptions\MissingFieldException;

abstract class CSVImporter
{
    public static function getImporter($type)
    {
        if ($type == 'products') {
            return new Products();
        }
        else if ($type == 'users') {
            return new Users();
        }
        else if ($type == 'movements') {
            return new Movements();
        }

        Log::error('Unexpected type for CSV import: ' . $type);
        return null;
    }

    private function guessCsvFileSeparator($path)
    {
        $contents = fopen($path, 'r');
        if ($contents === false) {
            return null;
        }

        $separators = [',', ';', "\t"];
        $target_separator = null;

        while (!feof($contents) && is_null($target_separator)) {
            $char = fgetc($contents);
            if (in_array($char, $separators)) {
                $target_separator = $char;
                break;
            }
        }

        fclose($contents);

        return $target_separator;
    }

    protected function storeUploadedFile($request, $parameters)
    {
        try {
            $f = $request->file('file', null);
            if (is_null($f) || $f->isValid() == false) {
                return $this->errorResponse(_i('File non caricato correttamente, possibili problemi con la dimensione'));
            }

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath . '/' . $filename;
            $sample_line = '';

            $target_separator = $this->guessCsvFileSeparator($path);
            if (is_null($target_separator)) {
                return $this->errorResponse(_i('Impossibile interpretare il file'));
            }

            $reader = Reader::createFromPath($path, 'r');
            $reader->setDelimiter($target_separator);
            foreach($reader->getRecords() as $line) {
                $sample_line = $line;
                break;
            }

            $parameters['path'] = $path;
            $parameters['columns'] = $sample_line;

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
            $found = false;

            foreach ($columns as $index => $field) {
                if ($field == $s) {
                    $ret[] = $index;
                    $found = true;
                    break;
                }
            }

            if ($found == false) {
                $ret[] = -1;
            }
        }

        return $ret;
    }

    protected function initRead($request)
    {
        $path = $request->input('path');
        $columns = $request->input('column');

        $testable = [];
        foreach($this->fields() as $key => $meta) {
            $mandatory = $meta->mandatory ?? false;
            if ($mandatory) {
                $testable[] = $key;
            }
        }

        $tested = $this->getColumnsIndex($columns, $testable);
        foreach($tested as $t) {
            if ($t == -1) {
                throw new MissingFieldException(1);
            }
        }

        $target_separator = $this->guessCsvFileSeparator($path);
        if (is_null($target_separator)) {
            throw new \Exception(_i('Impossibile interpretare il file'), 1);
        }

        $reader = Reader::createFromPath($path, 'r');
        $reader->setDelimiter($target_separator);

        return [$reader, $columns];
    }

    protected abstract function fields();
    public abstract function testAccess($request);
    public abstract function guess($request);
    public abstract function select($request);
    public abstract function formatSelect($parameters);
    public abstract function run($request);
}
