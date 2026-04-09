<?php

/**
 * Questa struttura dati è comune alle diverse tipologie di Printer, e ne
 * include tutte le possibili varianti ed opzioni. Questo per accentrare in un
 * posto solo tutti i possibili default, e rendere un po' più leggibile il
 * codice dei Printer stessi
 */

namespace App\Printers;

use Illuminate\Database\Eloquent\Model;

use App\Formatters\User;
use App\Formatters\Order;

class PrintParams
{
    public array $request;
    public Model $obj;
    public string $action;
    public string $subtype;
    public string $status;
    public string $include_missing;
    public bool $isolate_friends;
    public bool $extra_modifiers;
    public array $required_fields;
    public object $fields;

    public function __construct($request, $obj)
    {
        $this->request = $request;
        $this->obj = $obj;
        $this->action = $request['action'] ?? 'download';
        $this->subtype = $request['format'] ?? 'pdf';
        $this->status = $request['status'] ?? 'pending';
        $this->include_missing = $request['include_missing'] ?? 'no';
        $this->isolate_friends = ($request['isolate_friends'] ?? false);
        $this->extra_modifiers = ($request['extra_modifiers'] ?? false);

        $this->required_fields = $request['fields'] ?? [];
        if (empty($this->required_fields)) {
            $this->required_fields = $this->autoGuessFields($this->obj);
        }

        $this->fields = $this->splitFields($this->required_fields);
    }

    /**
     * Questo serve a separare le colonne per utenti e prodotti quando si
     * generano i Dettagli Consegne che contengono tutto
     */
    private function splitFields(array $fields): object
    {
        $formattable_user = User::formattableColumns('all');
        $formattable_product = Order::formattableColumns('summary');

        $ret = (object) [
            'headers' => [],
            'user_columns' => [],
            'product_columns' => [],
            'user_columns_names' => [],
            'product_columns_names' => [],
        ];

        $user_headers = [];
        $product_headers = [];

        /*
            Se l'array dei campi esplicitamente richiesti è vuoto, lo popolo con i
            campi di default (quelli che vengono selezionati anche dal pannello web)
        */
        if (empty($fields)) {
            list($useless, $selected_user) = flaxComplexOptions($formattable_user);
            list($useless, $selected_product) = flaxComplexOptions($formattable_product);
            $fields = array_merge($selected_user, $selected_product);
        }

        foreach ($fields as $f) {
            if (isset($formattable_user[$f])) {
                $ret->user_columns[] = $f;
                $ret->user_columns_names[] = $formattable_user[$f]->name;
                $user_headers[] = $formattable_user[$f]->name;
            }
            else {
                $ret->product_columns[] = $f;
                $ret->product_columns_names[] = $formattable_product[$f]->name;
                $product_headers[] = $formattable_product[$f]->name;
            }
        }

        /*
            Non necessariamente $fields è ordinato per attributi dell'utente e del
            prodotto: nel ciclo sopra li raccolgo separatamente, e poi riunisco le
            intestazioni nell'ordine giusto
        */
        $ret->headers = array_merge($user_headers, $product_headers);

        return $ret;
    }

    /**
     * Qui si enumerano le informazioni da includere nei files esportati quando
     * non ne viene esplicitamente richiesta nessuna (e.g. quando genero il file
     * scaricabile dal fornitore)
     */
    private function autoGuessFields(Model $order): array
    {
        $guessed_fields = [];

        $guessed_fields[] = 'fullname';

        if ($order->products->first(fn ($p) => !empty($p->code)) != null) {
            $guessed_fields[] = 'code';
        }

        $guessed_fields[] = 'name';
        $guessed_fields[] = 'quantity';

        if ($order->products->first(fn ($p) => $p->package_size != 0) != null) {
            $guessed_fields[] = 'boxes';
        }

        $guessed_fields[] = 'measure';
        $guessed_fields[] = 'unit_price';
        $guessed_fields[] = 'price';

        return $guessed_fields;
    }

    public function getPriceOffset($absolute = true): int|null
    {
        $price_offset = null;

        if ($absolute) {
            $fields = $this->required_fields;
        }
        else {
            $fields = $this->fields->product_columns;
        }

        if (in_array('price', $fields)) {
            $price_offset = array_search('price', $fields);
        }

        return $price_offset;
    }
}
