<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;
use Log;

use App\Events\SluggableCreating;

class MovementType extends Model
{
    use SoftDeletes, GASModel, SluggableID, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function hasPayment($type)
    {
        $valid = paymentsByType($this->id);
        return array_key_exists($type, $valid);
    }

    private function applyFunction($obj, $movement, $op)
    {
        /*
            Inutile perdere tempo su movimenti che non intaccano i bilanci...
        */
        if ($movement->amount == 0) {
            return;
        }

        if (is_null($obj)) {
            Log::error(_i('Applicazione movimento su oggetto nullo: %s', $movement->id));
            return;
        }

        if ($op->operation == 'decrement') {
            $amount = $movement->amount * -1;
        }
        else if ($op->operation == 'increment') {
            $amount = $movement->amount;
        }
        else {
            return;
        }

        $obj->alterBalance($amount, $movement->currency, $op->field);
    }

    public function apply($movement)
    {
        $ops = json_decode($this->function);

        foreach($ops as $o) {
            if ($o->method != $movement->method) {
                continue;
            }

            foreach($o->sender->operations as $op) {
                $this->applyFunction($movement->sender, $movement, $op);
            }

            foreach($o->target->operations as $op) {
                $this->applyFunction($movement->target, $movement, $op);
            }

            if (!empty($o->master->operations)) {
                $currentgas = currentAbsoluteGas();

                foreach($o->master->operations as $op) {
                    $this->applyFunction($currentgas, $movement, $op);
                }
            }

            break;
        }
    }

    public function altersBalances($movement, $peer)
    {
        $ops = json_decode($this->function);

        foreach($ops as $o) {
            if ($o->method != $movement->method) {
                continue;
            }

            return (!empty($o->$peer->operations));
        }

        return false;
    }

    public function transactionType($movement, $peer)
    {
        $ops = json_decode($this->function);

        foreach($ops as $o) {
            if ($o->method != $movement->method) {
                continue;
            }

            foreach($o->$peer->operations as $op) {
                if ($op->operation == 'increment') {
                    return 'credit';
                }
                else {
                    return 'debit';
                }
            }

            break;
        }

        if ($peer == 'sender') {
            return 'debit';
        }
        else {
            return 'credit';
        }
    }
}
