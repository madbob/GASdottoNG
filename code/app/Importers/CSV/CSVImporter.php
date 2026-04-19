<?php

namespace App\Importers\CSV;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Info;

use App\Exceptions\MissingFieldException;

abstract class CSVImporter
{
    private $reader;

    public function extraInformations()
    {
        return null;
    }

    /**
     * Come parametro di questa funzione si usa il nome della sotto-classe di
     * CSVImporter che si intende usare, scritto tutto minuscolo
     */
    public static function getImporter($type)
    {
        $classname = 'App\\Importers\\CSV\\' . ucwords($type);
        return new $classname();
    }

    private function startReader(array $request, string $path)
    {
        $reader = Reader::from($path, 'r');
        $options = Info::getDelimiterStats($reader, [',', ';', "\t"]);
        arsort($options);
        $target_separator = array_keys($options)[0];
        $reader->setDelimiter($target_separator);

        $skip = isset($request['skip_header']);
        if ($skip) {
            $reader->setHeaderOffset(0);
        }

        $reader->skipEmptyRecords();
        return $reader;
    }

    /**
     * Se la sotto-classe specifica un attributo "sorted_fields" tra i
     * parametri, pre-popolo l'array dei campi selezionati in fase di
     * revisione del CSV. Questo viene usato, ad esempio, dall'importer
     * "Products" per inizializzare l'importazione dei prodotti di un certo
     * fornitore usando sempre le stesse colonne (anziché doverle
     * riassegnare a mano ogni volta)
     */
    private function retrievePreSortedFields($parameters)
    {
        $selected = [];
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
                    'label' => __('texts.imports.ignore_slot'),
                    'name' => 'none',
                ];
            }
        }

        return $selected;
    }

    private function retrievePreSelectedFields(&$parameters)
    {
        if (isset($parameters['sorted_fields']) && !empty($parameters['sorted_fields'])) {
            return $this->retrievePreSortedFields($parameters);
        }

        $selected = [];

        /*
            Qui cerco di auto-assegnare le colonne in funzione
            dell'intestazione nella prima riga del CSV. Se funziona, devo
            badare a non considerare la prima riga (perché, appunto,
            contiene una intestazione e non dei dati da importare)
        */
        $mapped_cols = 0;

        foreach ($parameters['columns'] as $c) {
            $found = false;

            foreach ($parameters['sorting_fields'] as $sf => $sf_meta) {
                if ($sf_meta->label == $c) {
                    $selected[] = (object) [
                        'label' => $c,
                        'name' => $sf,
                    ];

                    $mapped_cols++;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $selected[] = (object) [
                    'label' => __('texts.imports.ignore_slot'),
                    'name' => 'none',
                ];
            }
        }

        if ($mapped_cols > count($parameters['columns']) / 2) {
            $parameters['skip_header'] = true;
        }

        return $selected;
    }

    protected function storeUploadedFile(array $request, array $parameters)
    {
        try {
            $f = $request['file'];
            if (is_null($f) || !$f->isValid()) {
                throw new \InvalidArgumentException('File non caricato correttamente, possibili problemi con la dimensione');
            }

            $parameters = array_merge($parameters, [
                'skip_header' => false,
            ]);

            $filepath = sys_get_temp_dir();
            $filename = $f->getClientOriginalName();
            $f->move($filepath, $filename);
            $path = $filepath . '/' . $filename;

            $this->reader = $this->startReader($request, $path);
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

    protected function initRead(array $request)
    {
        $path = $request['path'];
        $columns = $request['column'];

        $testable = $this->mandatoryFields();
        $tested = $this->getColumnsIndex($columns, $testable);

        foreach ($tested as $t) {
            if ($t == -1) {
                throw new MissingFieldException(1);
            }
        }

        $this->reader = $this->startReader($request, $path);

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

        /*
            Per ogni riga, elimino le chiavi dell'array e lascio solo gli indici
            numerici.
            Questo perché se è stato definito un header (in startReader()) mi
            troverei appunto le intestazioni come chiavi dell'array, e questo
            renderebbe un po' più complicato maneggiarlo successivamente
        */
        $ret = array_map(fn ($row) => array_values($row), $ret);

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

    abstract public function testAccess(array $request);

    abstract public function guess(array $request);

    abstract public function select(array $request);

    abstract public function formatSelect($parameters);

    abstract public function run(array $request);
}
