<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
    @property-read BookedProduct|Booking|null $target
 */
class ModifiedValue extends Model
{
    public function modifier(): BelongsTo
    {
        return $this->belongsTo('App\Modifier');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIsVariableAttribute()
    {
        if ($this->modifier->modifierType->getAttribute('hidden')) {
            return false;
        }
        else {
            return $this->modifier->value == 'absolute' && $this->modifier->applies_target == 'order';
        }
    }

    public function getEffectiveAmountAttribute()
    {
        switch ($this->modifier->arithmetic) {
            case 'sum':
            case 'passive':
                return $this->amount;

            case 'apply':
            case 'sub':
                return $this->amount * -1;

            default:
                throw new \DomainException('Unexpected arithmetic for modifier: ' . $this->modifier->arithmetic);
        }
    }

    public function getDescriptiveNameAttribute()
    {
        if ($this->modifier->modifierType->getAttribute('hidden')) {
            return $this->modifier->modifierType->name;
        }
        else {
            return $this->modifier->modifierType->name . ' ' . $this->modifier->target->printableName() . ': ' . $this->modifier->name;
        }
    }

    public function getSummary()
    {
        return $this->target->getModifiedRelations();
    }

    public static function aggregateByType($collection)
    {
        return $collection->reduce(function ($carry, $value) {
            $id = $value->modifier->modifierType->id;

            if (! isset($carry[$id])) {
                /*
                    Qui divido tra il valore che impatta sul totale della
                    prenotazione, il valore passivo che non deve essere sommato,
                    ed il valore complessivo che è la somma dei due e serve a
                    rappresentare il modificatore nella sua interezza
                */
                $carry[$id] = (object) [
                    'name' => $value->modifier->modifierType->name,
                    'amount' => 0,
                    'passive_amount' => 0,
                    'total_amount' => 0,
                ];
            }

            $effective = $value->effective_amount;

            if ($value->modifier->arithmetic == 'passive') {
                $carry[$id]->passive_amount += $effective;
            }
            else {
                $carry[$id]->amount += $effective;
            }

            $carry[$id]->total_amount += $effective;

            return $carry;
        }, []);
    }

    /*
        Per formattare il valore di un oggetto generato con
        self::aggregateByType()
    */
    public static function printAggregated($am)
    {
        if ($am->passive_amount) {
            return printablePriceCurrency($am->passive_amount, ',');
        }
        else {
            return printablePriceCurrency($am->amount, ',');
        }
    }

    /*
        Un valore "passivo" o con valore a 0 non implica nessuna operazione nel
        calcolo di totali e somme
    */
    public function activeMath()
    {
        return $this->modifier->arithmetic != 'passive' && $this->effective_amount != 0;
    }

    public function sumAmount($value)
    {
        if ($this->activeMath()) {
            return $value + $this->effective_amount;
        }
        else {
            return $value;
        }
    }

    public static function sumAmounts($values, $starting_value = 0)
    {
        return $values->reduce(function ($carry, $item) {
            return $item->sumAmount($carry);
        }, $starting_value);
    }

    private function wireMovement($class_type)
    {
        $rel = $this->target->getModifiedRelations();

        switch ($class_type) {
            case 'App\Gas':
                $ret = $rel->user->gas;
                break;

            case 'App\User':
                $ret = $rel->user;
                break;

            case null:
            case '':
                $ret = null;
                break;

            default:
                $ret = $rel->supplier;
                break;
        }

        return $ret;
    }

    public function generateMovement($master_movement)
    {
        if ($this->activeMath() === false) {
            return;
        }

        $type = $this->modifier->movementType;
        if (is_null($type)) {
            return;
        }

        $movement = new Movement();
        $movement->type = $type->id;

        $sender = $this->wireMovement($type->sender_type);
        if ($sender) {
            $movement->sender_type = get_class($sender);
            $movement->sender_id = $sender->id;
        }

        $target = $this->wireMovement($type->target_type);
        if ($target) {
            $movement->target_type = get_class($target);
            $movement->target_id = $target->id;
        }

        $movement->amount = $this->effective_amount;
        $movement->currency_id = $master_movement->currency_id;
        $movement->date = Carbon::today();
        $movement->method = $master_movement->method;
        $movement->related_id = $master_movement->id;

        $movement->save();
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
    public static function organizeForProducts(&$products_modifiers, Collection $target_modifiers, $key): void
    {
        $actual_modifiers = $target_modifiers->filter(fn ($pmod) => $pmod->target_type == BookedProduct::class);

        foreach ($actual_modifiers as $pmod) {
            $mod_id = $pmod->modifier->modifier_type_id;
            $product_id = $pmod->target->product_id;

            if (! isset($products_modifiers[$mod_id])) {
                $products_modifiers[$mod_id] = (object) [
                    'label' => sprintf('%s (%s)', $pmod->modifier->modifierType->name, ($key == 'pending' ? __('texts.orders.booking.statuses.booked') : __('texts.orders.booking.statuses.shipped'))),
                ];
            }

            if (! isset($products_modifiers[$mod_id]->$key)) {
                $products_modifiers[$mod_id]->$key = [];
            }

            if (! isset($products_modifiers[$mod_id]->$key[$product_id])) {
                $products_modifiers[$mod_id]->$key[$product_id] = 0;
            }

            $products_modifiers[$mod_id]->$key[$product_id] += $pmod->effective_amount;
        }
    }
}
