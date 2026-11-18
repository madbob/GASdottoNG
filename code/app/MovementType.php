<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\SluggableID;

class MovementType extends Model
{
    use Cachable, GASModel, HasFactory, SluggableID, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class, 'type');
    }

    public function hasPayment($type)
    {
        $valid = paymentsByType($this->id);

        return array_key_exists($type, $valid);
    }

    /*
        Per identificare i tipi di movimento contabile che possono essere
        utilizzati in fase di pagamento di una fattura.
        Da notare che - controintuitivamente - vengono accolti anche i movimenti
        circoscritti al GAS e che non coinvolgono il fornitore (o la fattura
        stessa), per dare la possibilità di registrare altri movimenti in
        qualche modo correlati (e.g. il costo del bonifico bancario, che non
        intacca il saldo del fornitore ma solo quello del GAS)
    */
    public function validForInvoices()
    {
        return ($this->visibility && (
            ($this->sender_type == Gas::class && ($this->target_type == Supplier::class || $this->target_type == Invoice::class)) ||
            ($this->sender_type == Supplier::class && $this->target_type == Gas::class) ||
            ($this->sender_type == Gas::class && $this->target_type == null) ||
            ($this->sender_type == null && $this->target_type == Gas::class)
        )) || $this->id == 'invoice-payment';
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
            Log::error('Applicazione movimento su oggetto nullo: ' . $movement->id);

            return;
        }

        if ($op->operation == 'decrement') {
            $amount = $movement->amount * -1;
        }
        elseif ($op->operation == 'increment') {
            $amount = $movement->amount;
        }
        else {
            return;
        }

        $obj->alterBalance($amount, $movement->currency, $op->field);
    }

    public function opsByMethod($method)
    {
        $ops = json_decode($this->function);

        foreach ($ops as $o) {
            if ($o->method == $method) {
                return $o;
            }
        }

        return null;
    }

    public function apply($movement)
    {
        $o = $this->opsByMethod($movement->method);

        if ($o) {
            foreach ($o->sender->operations as $op) {
                $this->applyFunction($movement->sender, $movement, $op);
            }

            foreach ($o->target->operations as $op) {
                $this->applyFunction($movement->target, $movement, $op);
            }

            if (! empty($o->master->operations)) {
                $currentgas = currentAbsoluteGas();

                foreach ($o->master->operations as $op) {
                    $this->applyFunction($currentgas, $movement, $op);
                }
            }
        }
    }

    public function altersBalances($movement, $peer)
    {
        $o = $this->opsByMethod($movement->method);

        if ($o) {
            return ! empty($o->$peer->operations);
        }
        else {
            return false;
        }
    }

    public function transactionType($movement, $peer)
    {
        $o = $this->opsByMethod($movement->method);

        if ($o) {
            foreach ($o->$peer->operations as $op) {
                return ($op->operation == 'increment') ? 'credit' : 'debit';
            }
        }

        return ($peer == 'sender') ? 'debit' : 'credit';
    }

    public function hasBrokenModifier()
    {
        if ($this->id == 'booking-payment') {
            $types = Modifier::has('movementType')->pluck('movement_type_id')->unique();
            $types = MovementType::whereIn('id', $types)->get();

            foreach ($types as $type) {
                if ($type->overlapsPaymentMethods($this) === false) {
                    return true;
                }
            }
        }
        else {
            $has_modifiers = Modifier::where('movement_type_id', $this->id)->exists();
            if ($has_modifiers) {
                $booking_payment_type = movementTypes('booking-payment');
                if ($this->overlapsPaymentMethods($booking_payment_type) === false) {
                    return true;
                }
            }
        }

        return false;
    }

    /*
        Questa funzione controlla che tutti i metodi di pagamento previsti dal
        tipo di movimento passato come parametro siano attivi per il tipo di
        movimento corrente, ma non controlla il contrario (né deve farlo)
    */
    public function overlapsPaymentMethods($other_type): bool
    {
        $methods_local = array_keys(paymentsByType($this->id));
        $methods_other = array_keys(paymentsByType($other_type->id));

        foreach ($methods_other as $mo) {
            if (in_array($mo, $methods_local) === false) {
                return false;
            }
        }

        return true;
    }
}
