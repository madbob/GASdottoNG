<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

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
            'product,minor,none' => ['', ''],
            'product,minor,quantity' => [_i("Se la quantità di prodotto è minore di"), _i('Prodotti')],
            'product,minor,price' => [_i("Se il prezzo del prodotto è minore di"), $currency],
            'product,minor,weight' => [_i("Se il peso del prodotto è minore di"), _i('Chili')],
            'booking,minor,none' => ['', ''],
            'booking,minor,quantity' => [_i("Se la quantità di prodotti nella prenotazione è minore di"), _i('Prodotti')],
            'booking,minor,price' => [_i("Se il valore della prenotazione è minore di"), $currency],
            'booking,minor,weight' => [_i("Se il peso della prenotazione è minore di"), _i('Chili')],
            'order,minor,none' => ['', ''],
            'order,minor,quantity' => [_i("Se la quantità di prodotti nell'ordine è minore di"), _i('Prodotti')],
            'order,minor,price' => [_i("Se il valore dell'ordine è minore di"), $currency],
            'order,minor,weight' => [_i("Se il peso dell'ordine è minore di"), _i('Chili')],
            'product,major,none' => ['', ''],
            'product,major,quantity' => [_i("Se la quantità di prodotto è maggiore di"), _i('Prodotti')],
            'product,major,price' => [_i("Se il prezzo del prodotto è maggiore di"), $currency],
            'product,major,weight' => [_i("Se il peso del prodotto è maggiore di"), _i('Chili')],
            'booking,major,none' => ['', ''],
            'booking,major,quantity' => [_i("Se la quantità di prodotti nella prenotazione è maggiore di"), _i('Prodotti')],
            'booking,major,price' => [_i("Se il valore della prenotazione è maggiore di"), $currency],
            'booking,major,weight' => [_i("Se il peso della prenotazione è maggiore di"), _i('Chili')],
            'order,major,none' => ['', ''],
            'order,major,quantity' => [_i("Se la quantità di prodotti nell'ordine è maggiore di"), _i('Prodotti')],
            'order,major,price' => [_i("Se il valore dell'ordine è maggiore di"), $currency],
            'order,major,weight' => [_i("Se il peso dell'ordine è maggiore di"), _i('Chili')],
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
        return sprintf('%s,%s,%s,%s,%s,%s', $this->applies_target, $this->scale, $this->applies_type, $this->arithmetic, $this->distribution_target, $this->value);
    }

    private function applyDefinition($amount, $definition, $target, $subtarget, $attribute)
    {
        $rounding = 4;

        if ($this->value == 'percentage') {
            $original_amount = $amount;
            $amount = round(($amount * $definition->amount) / 100, $rounding);
            Log::debug($original_amount . ' -> ' . $amount);
        }
        else {
            if ($this->distribution_target == 'order') {
                $order_attribute = $this->applies_type;
                if ($order_attribute == 'none') {
                    $order_attribute = 'price';
                }

                $amount = $target->$order_attribute == 0 ? $definition->amount : round(($definition->amount * $subtarget->$attribute) / $target->$order_attribute, $rounding);
            }
            else {
                $amount = $definition->amount;
            }
        }

        return $amount;
    }

    public function apply($booking, $aggregate_data)
    {
        if (!isset($aggregate_data->orders[$booking->order_id])) {
            return null;
        }

        if ($this->definitions->isEmpty()) {
            return null;
        }

        switch($this->applies_target) {
            case 'order':
                $check_target = $aggregate_data->orders[$booking->order_id];
                break;

            case 'booking':
                if (!isset($aggregate_data->orders[$booking->order_id]->bookings[$booking->id])) {
                    return null;
                }

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
                $sub_mod_target = null;
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

                $sub_mod_target = null;
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
            if ($attribute == 'none') {
                $attribute = 'price';
            }

            $mod_attribute = 'price';
        }

        $check_value = $check_target->$attribute;
        if ($check_value == 0) {
            return null;
        }

        $altered_amount = $mod_target->$mod_attribute;
        $target_definition = null;

        if ($this->scale == 'minor') {
            foreach($this->definitions as $def) {
                if ($check_value < $def->threshold) {
                    $target_definition = $def;
                    break;
                }
            }
        }
        else {
            foreach($this->definitions as $def) {
                if ($check_value > $def->threshold) {
                    $target_definition = $def;
                    break;
                }
            }
        }

        if (is_null($target_definition) == false) {
            $altered_amount = $this->applyDefinition($altered_amount, $target_definition, $mod_target, $sub_mod_target, $attribute);
        }
        else {
            Log::error('Unable to apply any threshold for modifier ' . $this->id);
        }

        $modifier_value = $obj_mod_target->modifiedValues->firstWhere('modifier_id', $this->id);
        if (is_null($modifier_value)) {
            $modifier_value = new ModifiedValue();
            $modifier_value->setRelation('modifier', $this);
            $obj_mod_target->modifiedValues->push($modifier_value);
        }

        $modifier_value->modifier_id = $this->id;
        $modifier_value->amount = $altered_amount;
        $modifier_value->target_type = get_class($obj_mod_target);
        $modifier_value->target_id = $obj_mod_target->id;
        $modifier_value->save();

        return $modifier_value;
    }
}
