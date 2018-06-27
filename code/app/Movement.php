<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use Auth;
use Log;

use App\GASModel;
use App\MovementType;

class Movement extends Model
{
    use GASModel;

    /*
        Per verificare il corretto salvataggio di un movimento, non consultare
        l'ID dell'oggetto ma il valore di questo attributo dopo aver invocato
        la funzione save()
        Ci sono casi particolari (cfr. il salvataggio del pagamento dei una
        prenotazione per un ordine aggregato) in cui il singolo movimento non
        viene realmente salvato ma elaborato (in questo caso: scomposto in più
        movimenti), dunque l'oggetto in sé non viene riportato sul database
        anche se l'operazione, nel suo complesso, è andata a buon fine.
        Vedasi MovementsKeeper::saving(), MovementsController::store(), o le
        pre-callbacks definite in MovementType::systemTypes()
    */
    public $saved = false;

    protected $casts = [
        'amount' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('registerer', function($query) {
                $user = Auth::user();
                if (is_null($user))
                    return;
                $query->withTrashed()->where('gas_id', $user->gas->id);
            });
        });
    }

    public function sender()
    {
        if ($this->sender_type && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->sender_type)))
            return $this->morphTo()->withoutGlobalScopes()->withTrashed();
        else
            return $this->morphTo()->withoutGlobalScopes();
    }

    public function target()
    {
        if ($this->target_type && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->target_type)))
            return $this->morphTo()->withoutGlobalScopes()->withTrashed();
        else
            return $this->morphTo()->withoutGlobalScopes();
    }

    public function registerer()
    {
        return $this->belongsTo('App\User');
    }

    public function getPaymentIconAttribute()
    {
        $types = MovementType::payments();
        $icon = 'glyphicon-question-sign';
        $name = '???';

        foreach ($types as $id => $details) {
            if ($this->method == $id) {
                $icon = $details->icon;
                $name = $details->name;
                break;
            }
        }

        return '<span class="glyphicon ' . $icon . '" aria-hidden="true"></span> ' . $name;
    }

    public function getTypeMetadataAttribute()
    {
        return MovementType::types($this->type, true);
    }

    public function getValidPaymentsAttribute()
    {
        $ret = MovementType::paymentsByType($this->type);
        $ret[$this->method]->checked = true;
        return $ret;
    }

    public function printableName()
    {
        if (empty($this->date) || strstr($this->date, '0000-00-00') !== false)
            return 'Mai';
        else
            return sprintf('%s | %s | %s', $this->printableDate('date'), printablePriceCurrency($this->amount), $this->payment_icon);
    }

    public function printableType()
    {
        return $this->type_metadata->name;
    }

    public function printablePayment()
    {
        $types = MovementType::payments();
        foreach ($types as $id => $details) {
            if ($this->method == $id) {
                return $details->name;
            }
        }
        return '???';
    }

    /*
        $peer può essere "target" o "sender".
        La funzione ritorna "credit" o "debit" a seconda che incida
        positivamente o negativamente sul soggetto desiderato coinvolto nel
        movimento
    */
    public function transactionType($peer)
    {
        return $this->type_metadata->transactionType($this, $peer);
    }

    /*
        La funzione ritorna "target" o "sender" a seconda del ruolo dell'oggetto
        passato come parametro all'interno della transazione.
        Vengono presi in considerazioni i proxy contabili
    */
    public function transationRole($obj_peer)
    {
        if ($this->sender_id == $obj_peer->id)
            return 'sender';
        if ($this->target_id == $obj_peer->id)
            return 'target';

        $t = $this->sender;
        if ($t != null) {
            $t = $t->getBalanceProxy();
            if ($t && $t->id == $obj_peer->id)
                return 'sender';
        }

        $t = $this->target;
        if ($t != null) {
            $t = $t->getBalanceProxy();
            if ($t && $t->id == $obj_peer->id)
                return 'target';
        }

        return null;
    }

    public static function generate($type, $sender, $target, $amount)
    {
        $ret = new self();
        $ret->type = $type;
        $ret->sender_type = get_class($sender);
        $ret->sender_id = $sender->id;
        $ret->target_type = get_class($target);
        $ret->target_id = $target->id;

        $type_descr = MovementType::types($type);
        if ($type_descr->fixed_value != false) {
            $ret->amount = $type_descr->fixed_value;
        }
        else {
            $ret->amount = $amount;
        }

        $ret->date = date('Y-m-d');
        $ret->notes = $type_descr->default_notes;
        $ret->method = MovementType::defaultPaymentByType($type);

        return $ret;
    }

    public function parseRequest($request)
    {
        $metadata = $this->type_metadata;
        if (isset($metadata->callbacks['parse'])) {
            $metadata->callbacks['parse']($this, $request);
        }
    }

    public function apply()
    {
        $this->type_metadata->apply($this);
    }
}
