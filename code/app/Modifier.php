<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    use GASModel;

    public function modifierType()
    {
        return $this->belongsTo('App\ModifierType');
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getDefinitionsAttribute()
    {
        $ret = json_decode($this->definition);
        return collect($ret ?: []);
    }

    public function getNameAttribute()
    {
        $data = $this->definitions;

        if ($data->isEmpty()) {
            return _i('Nessun Valore');
        }

        if ($this->value == 'absolute') {
            $postfix = currentAbsoluteGas()->currency;
        }
        else {
            $postfix = '%';
        }

        $ret = [];

        foreach ($data as $d) {
            $ret[] = sprintf('%s%s', $d->amount, $postfix);
        }

        return join(' / ', $ret);
    }

    public static function descriptions()
    {
        /*
            Qui predispongo le stringhe descrittive per tutte le possibili
            combinazioni di valori, destinate a rendere più comprensibile la
            tabella delle soglie.
        */

        $currency = currentAbsoluteGas()->currency;

        $applies_labels = [
            'product,quantity' => [_i("Se la quantità di prodotto è minore di"), _i('Prodotti')],
            'product,price' => [_i("Se il prezzo del prodotto è minore di"), $currency],
            'product,weight' => [_i("Se il peso del prodotto è minore di"), _i('Chili')],
            'booking,quantity' => [_i("Se la quantità di prodotti nella prenotazione è minore di"), _i('Prodotti')],
            'booking,price' => [_i("Se il prezzo della prenotazione è minore di"), $currency],
            'booking,weight' => [_i("Se il peso della prenotazione è minore di"), _i('Chili')],
            'order,quantity' => [_i("Se la quantità di prodotti nell'ordine è minore di"), _i('Prodotti')],
            'order,price' => [_i("Se il prezzo dell'ordine è minore di"), $currency],
            'order,weight' => [_i("Se il peso dell'ordine è minore di"), _i('Chili')],
        ];

        $distribution_labels = [
            'sum,product,absolute' => [_i("somma al costo del prodotto"), $currency],
            'sum,booking,absolute' => [_i("somma al costo della prenotazione"), $currency],
            'sum,order,absolute' => [_i("somma al costo dell'ordine"), $currency],
            'sum,product,percentage' => [_i("somma al costo del prodotto"), '%'],
            'sum,booking,percentage' => [_i("somma al costo della prenotazione"), '%'],
            'sum,order,percentage' => [_i("somma al costo dell'ordine"), '%'],
            'sub,product,absolute' => [_i("sottrai al costo del prodotto"), $currency],
            'sub,booking,absolute' => [_i("sottrai al costo della prenotazione"), $currency],
            'sub,order,absolute' => [_i("sottrai al costo dell'ordine"), $currency],
            'sub,product,percentage' => [_i("sottrai al costo del prodotto"), '%'],
            'sub,booking,percentage' => [_i("sottrai al costo della prenotazione"), '%'],
            'sub,order,percentage' => [_i("sottrai al costo dell'ordine"), '%'],
        ];

        $labels = [];

        foreach($applies_labels as $applies_id => $applies_strings) {
            foreach($distribution_labels as $distribution_id => $distribution_strings) {
                $labels[$applies_id . ',' . $distribution_id] = array_merge($applies_strings, $distribution_strings);
            }
        }

        return $labels;
    }

    public function getDescriptionIndexAttribute()
    {
        return sprintf('%s,%s,%s,%s,%s', $this->applies_target, $this->applies_type, $this->modifierType->arithmetic, $this->distribution_target, $this->value);
    }
}
