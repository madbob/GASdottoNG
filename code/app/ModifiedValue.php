<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModifiedValue extends Model
{
    public function modifier()
    {
        return $this->belongsTo('App\Modifier');
    }

    public function getIsVariableAttribute()
    {
        return ($this->modifier->value == 'absolute' && $this->modifier->distribution_target == 'order');
    }

    public function getEffectiveAmountAttribute()
    {
        if ($this->modifier->modifierType->arithmetic == 'sum') {
            return $this->amount;
        }
        else {
            return $this->amount * -1;
        }
    }
}
