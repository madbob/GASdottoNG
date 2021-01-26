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
        return ($this->modifier->value == 'absolute' && $this->modifier->distribution_target == 'order');
    }

    public function getEffectiveAmountAttribute()
    {
        if ($this->modifier->arithmetic == 'sum') {
            return $this->amount;
        }
        else {
            return $this->amount * -1;
        }
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
}
