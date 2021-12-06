<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

class ModifiedValue extends Model
{
    public function modifier()
    {
        return $this->belongsTo('App\Modifier');
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getIsVariableAttribute()
    {
        return ($this->modifier->value == 'absolute' && $this->modifier->applies_target == 'order');
    }

    public function getEffectiveAmountAttribute()
    {
        switch($this->modifier->arithmetic) {
            case 'sum':
            case 'passive':
                return $this->amount;

            case 'apply':
            case 'sub':
                return $this->amount * -1;
        }
    }

    public function getDescriptiveNameAttribute()
    {
        return $this->modifier->modifierType->name . ' ' . $this->modifier->target->printableName() . ': ' . $this->modifier->name;
    }

    public function getSummary()
    {
        return $this->target->getModifiedRelations();
    }

    public static function aggregateByType($collection)
    {
        return $collection->reduce(function($carry, $value) {
            $id = $value->modifier->modifierType->id;

            if (!isset($carry[$id])) {
                $carry[$id] = (object) [
                    'name' => $value->modifier->modifierType->name,
                    'amount' => 0,
                ];
            }

            $carry[$id]->amount += $value->effective_amount;
            return $carry;
        }, []);
    }

    public static function sumAmounts($values, $starting_value = 0)
    {
        return $values->reduce(function($carry, $item) {
            if ($item->modifier->arithmetic != 'passive') {
                return $carry + $item->effective_amount;
            }
            else {
                return $carry;
            }
        }, $starting_value);
    }

    /*
        La struttura dati generata da questa funzione è qualcosa tipo:

        [
            ID del tipo di modificatore => (object) [
                'label' => etichetta da mostrate nell'intestazione della colonna
                'pending' => [
                    ID prodotto 1 => X euro,
                    ID prodotto 2 => Y euro,
                ]
                'shipped' => [
                    ID prodotto 1 => X euro,
                    ID prodotto 2 => Y euro,
                ]
            ]
        ]
    */
    public static function organizeForProducts(&$products_modifiers, $target_modifiers, $key) {
        foreach($target_modifiers as $pmod) {
            if ($pmod->target_type == BookedProduct::class) {
                $mod_id = $pmod->modifier->modifier_type_id;
                $product_id = $pmod->target->product_id;

                if (!isset($products_modifiers[$mod_id])) {
                    $products_modifiers[$mod_id] = (object) [
                        'label' => sprintf('%s (%s)', $pmod->modifier->modifierType->name, ($key == 'pending' ? _i('Prenotato') : _i('Consegnato'))),
                    ];
                }

                if (!isset($products_modifiers[$mod_id]->$key)) {
                    $products_modifiers[$mod_id]->$key = [];
                }

                if (!isset($products_modifiers[$mod_id]->$key[$product_id])) {
                    $products_modifiers[$mod_id]->$key[$product_id] = 0;
                }

                $products_modifiers[$mod_id]->$key[$product_id] += $pmod->effective_amount;
            }
        }
    }
}
