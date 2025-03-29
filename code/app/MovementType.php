<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Log;

use App\Events\SluggableCreating;

class MovementType extends Model
{
    use Cachable, GASModel, HasFactory, SluggableID, SoftDeletes;

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
            ($this->sender_type == 'App\Gas' && ($this->target_type == 'App\Supplier' || $this->target_type == 'App\Invoice')) ||
            ($this->sender_type == 'App\Supplier' && $this->target_type == 'App\Gas') ||
            ($this->sender_type == 'App\Gas' && $this->target_type == null) ||
            ($this->sender_type == null && $this->target_type == 'App\Gas')
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
            Log::error(_i('Applicazione movimento su oggetto nullo: %s', $movement->id));

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
                if ($op->operation == 'increment') {
                    return 'credit';
                }
                else {
                    return 'debit';
                }
            }
        }

        if ($peer == 'sender') {
            return 'debit';
        }
        else {
            return 'credit';
        }
    }

    public function hasBrokenModifier()
    {
        if ($this->id == 'booking-payment') {
            $types = Modifier::has('movementType')->pluck('movement_type_id')->unique();
            $types = MovementType::whereIn('id', $types)->get();

            foreach ($types as $type) {
                if ($type->overlapsPaymentMethods($this) == false) {
                    return true;
                }
            }
        }
        else {
            $has_modifiers = Modifier::where('movement_type_id', $this->id)->exists();
            if ($has_modifiers) {
                $booking_payment_type = movementTypes('booking-payment');
                if ($this->overlapsPaymentMethods($booking_payment_type) == false) {
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
    public function overlapsPaymentMethods($other_type)
    {
        $methods_local = array_keys(paymentsByType($this->id));
        $methods_other = array_keys(paymentsByType($other_type->id));

        foreach ($methods_other as $mo) {
            if (in_array($mo, $methods_local) == false) {
                return false;
            }
        }

        return true;
    }
}
