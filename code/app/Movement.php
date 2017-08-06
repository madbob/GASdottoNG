<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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

    public function sender()
    {
        return $this->morphTo();
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getPaymentIconAttribute()
    {
        $types = MovementType::payments();

        foreach ($types as $id => $details) {
            if ($this->method == $id) {
                return $details->icon;
            }
        }

        return 'glyphicon-question-sign';
    }

    public function getTypeMetadataAttribute()
    {
        return MovementType::types($this->type);
    }

    public function getValidPaymentsAttribute()
    {
        $movement_methods = MovementType::payments();
        $type_metadata = $this->type_metadata;
        $function = json_decode($type_metadata->function);
        $ret = [];

        foreach ($movement_methods as $method_id => $info) {
            if (isset($function[$method_id])) {
                $ret[$method_id] = $info;
            }
        }

        return $ret;
    }

    public function printableName()
    {
        return sprintf('%s | %f €', $this->printableDate('created_at'), $this->amount);
    }

    public function printableType()
    {
        return $this->type_metadata->name;
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
        } else {
            $ret->amount = $amount;
        }

        return $ret;
    }

    public function parseRequest(Request $request)
    {
        $metadata = $this->type_metadata;
        if (isset($metadata->callbacks['parse'])) {
            $metadata->callbacks['parse']($this, $request);
        }
    }

    public function apply()
    {
        $metadata = $this->type_metadata;
        $metadata->apply($this);
    }
}
