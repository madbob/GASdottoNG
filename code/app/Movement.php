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
        return $this->morphTo()->withTrashed();
    }

    public function target()
    {
        return $this->morphTo()->withTrashed();
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
        return MovementType::types($this->type);
    }

    public function getValidPaymentsAttribute()
    {
        return MovementType::paymentsByType($this->type);
    }

    public function printableName()
    {
        if (empty($this->registration_date) || strstr($this->registration_date, '0000-00-00') !== false)
            return 'Mai';
        else
            return sprintf('%s | %s € | %s', $this->printableDate('registration_date'), printablePrice($this->amount), $this->payment_icon);
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
