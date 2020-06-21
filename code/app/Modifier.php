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

    public function apply($booking, $aggregate_data)
    {
        switch($this->applies_target) {
            case 'order':
                $check_target = $aggregate_data->orders[$booking->order_id];
                break;

            case 'booking':
                $check_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id];
                break;

            case 'product':
                if (!isset($aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$this->target->id])) {
                    return null;
                }

                $check_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$this->target->id];
                break;
        }

        switch($this->distribution_target) {
            case 'order':
                $mod_target = $aggregate_data->orders[$booking->order_id];
                $sub_mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id];
                $obj_mod_target = $booking;
                break;

            case 'booking':
                $mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id];
                $obj_mod_target = $booking;
                break;

            case 'product':
                $product_target_id = $this->target->id;
                if (!isset($aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$product_target_id])) {
                    return null;
                }

                $mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$product_target_id];
                $obj_mod_target = $booking->products()->whereHas('product', function($query) use ($product_target_id) {
                    $query->where('product_id', $product_target_id);
                })->first();

                break;
        }

        if ($booking->status == 'shipped' || $booking->status == 'saved') {
            switch($this->applies_type) {
                case 'quantity':
                    $attribute = 'delivered';
                    break;
                case 'price':
                    $attribute = 'price_delivered';
                    break;
                case 'weight':
                    $attribute = 'weight_delivered';
                    break;
            }

            $mod_attribute = 'price_delivered';
        }
        else {
            $attribute = $this->applies_type;
            $mod_attribute = 'price';
        }

        $check_value = $check_target->$attribute;
        $altered_amount = $mod_target->$mod_attribute;

        foreach($this->definitions as $def) {
            if ($check_value < $def->threshold) {
                if ($this->value == 'percentage') {
                    $altered_amount = round((100 * $def->amount) / $altered_amount, 2);
                }
                else {
                    if ($this->distribution_target == 'order') {
                        $order_attribute = $this->applies_type;
                        $altered_amount = $mod_target->$order_attribute == 0 ? $def->amount : round(($def->amount * $sub_mod_target->$attribute) / $mod_target->$order_attribute, 2);
                    }
                    else {
                        $altered_amount = $def->amount;
                    }
                }

                break;
            }
        }

        $modifier_value = new ModifiedValue();
        $modifier_value->modifier_id = $this->id;
        $modifier_value->amount = $altered_amount;
        $modifier_value->target_type = get_class($obj_mod_target);
        $modifier_value->target_id = $obj_mod_target->id;
        $modifier_value->save();

        return $modifier_value;
    }
}
