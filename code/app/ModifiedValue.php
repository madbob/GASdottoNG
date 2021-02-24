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
}
