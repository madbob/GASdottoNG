<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Log;

class Modifier extends Model
{
    use GASModel, Cachable;

    public function modifierType(): BelongsTo
    {
        return $this->belongsTo('App\ModifierType');
    }

    public function movementType(): BelongsTo
    {
        return $this->belongsTo('App\MovementType');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDefinitionsAttribute()
    {
        $ret = json_decode($this->definition);
        return collect($ret ?: []);
    }

    public function getModelTypeAttribute()
    {
        $ret = strtolower(substr(strrchr($this->target_type, '\\'), 1));
        if ($ret == 'supplier') {
            $ret = 'order';
        }
        return $ret;
    }

    public function isTrasversal()
    {
        if ($this->active == false) {
            return false;
        }

        return ($this->value == 'absolute' && $this->applies_target == 'order');
    }

    public function getNameAttribute()
    {
        if ($this->active == false) {
            return _i('Nessun Valore');
        }

        $data = $this->definitions;

        $ret = [];

        foreach ($data as $d) {
            if ($this->value == 'percentage') {
                $postfix = '%';
                $amount = $d->amount;
            }
            else {
                $postfix = defaultCurrency()->symbol;
                $amount = printablePrice($d->amount);
            }

			if ($this->arithmetic == 'mass') {
				$postfix = $postfix . ' ' . _i('al KG');
			}

            $ret[] = sprintf('%s %s', $amount, $postfix);
        }

        return join(' / ', $ret);
    }

    public function getROShowURL()
    {
        return route('modifiers.show', $this->id);
    }

    public function getActiveAttribute()
    {
        $data = $this->definitions;

        if ($data->isEmpty()) {
            return false;
        }
        else {
            foreach($data as $d) {
                if ($d->amount != 0) {
                    return true;
                }
            }

            return false;
        }
    }

    public function getDescriptionIndexAttribute()
    {
        return sprintf('%s,%s,%s,%s,%s,%s,%s,%s,%s', $this->applies_type, $this->model_type, $this->applies_target, $this->scale, $this->applies_type, $this->arithmetic, $this->applies_target, $this->value, $this->distribution_type);
    }
}
