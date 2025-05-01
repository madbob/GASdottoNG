<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\TracksUpdater;

/**
    @property-read Aggregate|Delivery|Order|Product|Supplier|null $target
 */
class Modifier extends Model
{
    use Cachable, GASModel, TracksUpdater, HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function modifierType(): BelongsTo
    {
        return $this->belongsTo(ModifierType::class);
    }

    public function movementType(): BelongsTo
    {
        return $this->belongsTo(MovementType::class);
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDefinitionsAttribute()
    {
        $ret = json_decode($this->definition ?? []);

        /*
            Mantengo le soglie ordinate secondo il canone più comodo per la
            valutazione in Modifier::apply()
        */
        if ($this->scale == 'minor') {
            usort($ret, function ($a, $b) {
                return $a->threshold <=> $b->threshold;
            });
        }
        else {
            usort($ret, function ($a, $b) {
                return ($a->threshold <=> $b->threshold) * -1;
            });
        }

        return new Collection($ret ?: []);
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

        return $this->value == 'absolute' && $this->applies_target == 'order';
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

            if ($this->value == 'mass') {
                $postfix = $postfix . ' ' . _i('al KG');
            }

            $ret[] = sprintf('%s %s', $amount, $postfix);
        }

        return implode(' / ', $ret);
    }

    public function getROShowURL()
    {
        return route('modifiers.show', $this->id);
    }

    /*
        Questa funzione permette di capire a che livello della gerarchia si
        applica il modificatore.
        "order" e "booking" si riferiscono, rispettivamente, all'ordine nel suo
        complesso o alla specifica prenotazione.
        "product" si riferisce al prodotto all'interno della prenotazione.
        "global_product" si riferisce al prodotto complessivo nell'ordine
    */
    public function getCheckTargetLevel()
    {
        if ($this->target_type == Product::class) {
            if ($this->applies_type == 'order_price') {
                return 'order';
            }
            else {
                switch ($this->applies_target) {
                    case 'order':
                        return 'global_product';

                    default:
                        return 'product';
                }
            }
        }
        else {
            return $this->applies_target;
        }
    }

    public function getActiveAttribute()
    {
        $data = $this->definitions;

        if ($data->isEmpty()) {
            return false;
        }
        else {
            foreach ($data as $d) {
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
