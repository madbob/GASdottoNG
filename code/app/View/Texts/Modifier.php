<?php

namespace App\View\Texts;

use App\Product;

class Modifier
{
    private static function valueLabels()
    {
        return [
            'none' => '',
            'quantity' => _i('la quantità'),
            'price' => _i('il valore'),
            'order_price' => _i("il valore dell'ordine"),
            'weight' => _i('il peso'),
        ];
    }

    private static function targetsLabels()
    {
        return [
            'product,product' => '',
            'product,booking' => _i('di prodotto nella prenotazione'),
            'product,order' => _i("di prodotto nell'ordine"),
            'order,product' => '',
            'order,booking' => _i('della prenotazione'),
            'order,order' => _i("dell'ordine"),
            'aggregate,product' => '',
            'aggregate,booking' => _i('della prenotazione aggregata'),
            'aggregate,order' => _i("dell'ordine aggregato"),
            'delivery,product' => '',
            'delivery,booking' => _i('della prenotazione destinata al luogo'),
            'delivery,order' => _i("dell'ordine destinato al luogo"),
        ];
    }

    private static function scaleLabels()
    {
        return [
            'minor' => _i('è minore di'),
            'major' => _i('è maggiore di'),
        ];
    }

    private static function unitLabels($target)
    {
        $currency = defaultCurrency()->symbol;

        if (is_a($target, Product::class)) {
            $quantity_label = $target->measure->name;
        }
        else {
            $quantity_label = _i('Prodotti');
        }

        return [
            /*
                La 'X' serve a inizializzare l'input group nell'editor del
                modificatore, di fatto non viene mai visualizzata
            */
            'none' => 'X',
            'quantity' => $quantity_label,
            'price' => $currency,
            'order_price' => $currency,
            'weight' => _i('Chili'),
        ];
    }

    private static function distributionLabels()
    {
        $currency = defaultCurrency()->symbol;

        return [
            'sum,product,absolute' => [_i("somma al costo del prodotto"), $currency],
            'sum,booking,absolute' => [_i("somma al costo della prenotazione"), $currency],
            'sum,order,absolute' => [_i("somma al costo dell'ordine"), $currency],
            'sum,product,percentage' => [_i("somma al costo del prodotto"), '%'],
            'sum,booking,percentage' => [_i("somma al costo della prenotazione"), '%'],
            'sum,order,percentage' => [_i("somma al costo dell'ordine"), '%'],
			'sum,product,mass' => [_i("per ogni Kg di prodotto, somma"), $currency],
            'sum,booking,mass' => [_i("per ogni Kg nella prenotazione, somma"), $currency],
            'sum,order,mass' => [_i("per ogni Kg nell'ordine, somma"), $currency],

            'sub,product,absolute' => [_i("sottrai al costo del prodotto"), $currency],
            'sub,booking,absolute' => [_i("sottrai al costo della prenotazione"), $currency],
            'sub,order,absolute' => [_i("sottrai al costo dell'ordine"), $currency],
            'sub,product,percentage' => [_i("sottrai al costo del prodotto"), '%'],
            'sub,booking,percentage' => [_i("sottrai al costo della prenotazione"), '%'],
            'sub,order,percentage' => [_i("sottrai al costo dell'ordine"), '%'],
			'sub,product,mass' => [_i("per ogni Kg di prodotto, sottrai"), $currency],
            'sub,booking,mass' => [_i("per ogni Kg nella prenotazione, sottrai"), $currency],
            'sub,order,mass' => [_i("per ogni Kg nell'ordine, sottrai"), $currency],

            'passive,product,absolute' => [_i("rispetto al costo del prodotto, calcola"), $currency],
            'passive,booking,absolute' => [_i("rispetto al costo della prenotazione, calcola"), $currency],
            'passive,order,absolute' => [_i("rispetto al costo dell'ordine, calcola"), $currency],
            'passive,product,percentage' => [_i("rispetto al costo del prodotto, calcola"), '%'],
            'passive,booking,percentage' => [_i("rispetto al costo della prenotazione, calcola"), '%'],
            'passive,order,percentage' => [_i("rispetto al costo dell'ordine, calcola"), '%'],
			'passive,product,mass' => [_i("per ogni Kg di prodotto, calcola"), $currency],
            'passive,booking,mass' => [_i("per ogni Kg nella prenotazione, calcola"), $currency],
            'passive,order,mass' => [_i("per ogni Kg nell'ordine, calcola"), $currency],

            'apply,product,price' => [_i("applica il prezzo unitario"), $currency],
            'apply,booking,price' => [_i("applica il prezzo unitario"), $currency],
            'apply,order,price' => [_i("applica il prezzo unitario"), $currency],
        ];
    }

    private static function typesLabels()
    {
        return [
            'none' => '',
            'quantity' => _i('e distribuiscilo in base alle quantità prenotate'),
            'price' => _i('e distribuiscilo in base al valore delle prenotazioni'),
            'weight' => _i('e distribuiscilo in base al peso delle prenotazioni'),
        ];
    }

    public static function descriptions($target)
    {
        /*
            Qui predispongo le stringhe descrittive per tutte le possibili
            combinazioni di valori, destinate a rendere più comprensibile la
            tabella delle soglie.
        */

        $value_labels = self::valueLabels();
        $targets_labels = self::targetsLabels();
        $scale_labels = self::scaleLabels();
        $value_units = self::unitLabels($target);
        $distribution_labels = self::distributionLabels();
        $distribution_types = self::typesLabels();

        $labels = [];

        foreach($value_labels as $vl => $vs) {
            foreach($targets_labels as $tl => $ts) {
                foreach($scale_labels as $sl => $ss) {
                    foreach($value_units as $vu => $vus) {
                        foreach($distribution_labels as $dl => $ds) {
                            foreach($distribution_types as $dt => $dts) {
                                $key = sprintf('%s,%s,%s,%s,%s,%s', $vl, $tl, $sl, $vu, $dl, $dt);
                                $labels[$key] = [
                                    _i('Se %s %s %s', [$vs, $ts, $ss]),
                                    $vus,
                                    $ds[0],
                                    $ds[1],
                                    $dts,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $labels;
    }
}
