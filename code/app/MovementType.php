<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

use Auth;
use Log;

use App\Events\SluggableCreating;
use App\Gas;
use App\GASModel;

class MovementType extends Model
{
    use SoftDeletes, GASModel, SluggableID;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public static function payments()
    {
        $ret = [
            'cash' => (object) [
                'name' => _i('Contanti'),
                'identifier' => false,
                'icon' => 'glyphicon-euro',
                'active_for' => null
            ],
            'bank' => (object) [
                'name' => _i('Bonifico'),
                'identifier' => true,
                'icon' => 'glyphicon-link',
                'active_for' => null
            ],
            'credit' => (object) [
                'name' => _i('Credito Utente'),
                'identifier' => false,
                'icon' => 'glyphicon-ok',
                'active_for' => 'App\User'
            ],
        ];

        $gas = currentAbsoluteGas();
        if($gas->hasFeature('paypal')) {
            $ret['paypal'] = (object) [
                'name' => _i('PayPal'),
                'identifier' => true,
                'icon' => 'glyphicon-cloud-download',
                'active_for' => 'App\User'
            ];
        }

        return $ret;
    }

    public static function paymentsByType($type)
    {
        if ($type != null) {
            $metadata = self::types($type);
            $function = json_decode($metadata->function);
        }
        else {
            $function = null;
        }

        $movement_methods = MovementType::payments();
        $ret = [];

        foreach ($movement_methods as $method_id => $info) {
            $found = false;

            if ($function) {
                foreach($function as $f) {
                    if ($f->method == $method_id) {
                        $found = true;
                        break;
                    }
                }
            }
            else {
                $found = true;
            }

            if ($found)
                $ret[$method_id] = $info;
        }

        return $ret;
    }

    public static function defaultPaymentByType($type)
    {
        $metadata = self::types($type);
        $function = json_decode($metadata->function);

        foreach($function as $f) {
            if (isset($f->is_default) && $f->is_default) {
                return $f->method;
            }
        }

        if (empty($function))
            return null;
        else
            return $function[0]->method;
    }

    public static function initSystemTypes($types)
    {
        $currentuser = Auth::user();
        if(is_null($currentuser)) {
            $currentgas = Gas::get()->first();
        }
        else {
            $currentgas = $currentuser->gas;
        }

        foreach($types as $mov) {
            $mov->callbacks = [];

            switch($mov->id) {
                case 'deposit-pay':
                    $mov->fixed_value = $currentgas->getConfig('deposit_amount');

                    $mov->callbacks = [
                        'post' => function (Movement $movement) {
                            $sender = $movement->sender;
                            $sender->deposit_id = $movement->id;
                            $sender->save();
                        },
                        'delete' => function(Movement $movement) {
                            $sender = $movement->sender;
                            $sender->deposit_id = 0;
                            $sender->save();
                        }
                    ];

                    break;

                case 'deposit-return':
                    $mov->fixed_value = $currentgas->getConfig('deposit_amount');

                    $mov->callbacks = [
                        'post' => function (Movement $movement) {
                            $target = $movement->target;
                            $target->deposit_id = 0;
                            $target->save();
                        },
                        'delete' => function(Movement $movement) {
                            $sender = $movement->sender;

                            if ($sender->deposit_id == 0) {
                                $payment = Movement::where('type', 'deposit-pay')->where('sender_id', $sender->id)->first();
                                if ($payment) {
                                    $sender->deposit_id = $payment->id;
                                    $sender->save();
                                }
                            }
                        }
                    ];

                    break;

                case 'annual-fee':
                    $mov->fixed_value = $currentgas->getConfig('annual_fee_amount');

                    $mov->callbacks = [
                        'post' => function (Movement $movement) {
                            $sender = $movement->sender;
                            $sender->fee_id = $movement->id;
                            $sender->save();
                        },
                        'delete' => function(Movement $movement) {
                            $sender = $movement->sender;
                            $sender->fee_id = 0;
                            $sender->save();
                        }
                    ];

                    break;

                case 'booking-payment':
                    $mov->callbacks = [
                        /*
                            Il problema di fondo è che, a livello utente, un aggregato riceve un solo pagamento, dunque
                            devo a posteriori dividere tale pagamento tra le prenotazioni al suo interno creando
                            movimenti individuali.
                            Qui assumo che l'ammontare pagato per ciascuna prenotazione corrisponda col totale consegnato
                            della prenotazione stessa
                        */
                        'pre' => function (Movement $movement) {
                            if ($movement->target_type == 'App\Aggregate') {
                                $total = $movement->amount;
                                $aggregate = $movement->target;
                                $user = $movement->sender;
                                $m = null;

                                /*
                                    'handling_status' è un attributo fittizio allegato all'oggetto solo per determinare lo
                                    stato corrente della consegna. Cfr. la callback parse()
                                */
                                $handling_status = $movement->handling_status;
                                unset($movement->handling_status);

                                foreach ($aggregate->orders as $order) {
                                    $booking = $order->userBooking($user->id);
                                    if ($booking->exists == false) {
                                        continue;
                                    }

                                    if (isset($handling_status->{$booking->id})) {
                                        $delivered = $handling_status->{$booking->id};
                                    }
                                    else {
                                        $delivered = $booking->delivered;
                                    }

                                    if ($total < $delivered) {
                                        $delivered = $total;
                                    }

                                    $existing_movement = $booking->payment;
                                    if (is_null($existing_movement)) {
                                        $m = $movement->replicate();
                                        $m->target_id = $booking->id;
                                        $m->target_type = 'App\Booking';

                                        /*
                                            Qui devo ricaricare la relazione "target",
                                            altrimenti resta in memoria quella precedente
                                            (che faceva riferimento ad un Aggregate, dunque
                                            non è corretta e sul salvataggio spacca tutto)
                                        */
                                        $m->load('target');
                                    }
                                    else {
                                        $m = $existing_movement;
                                    }

                                    $m->amount = $delivered;
                                    $m->save();

                                    $total -= $delivered;
                                    if ($total <= 0) {
                                        break;
                                    }
                                }

                                /*
                                    Se avanza qualcosa, lo metto sulla fiducia nell'ultimo
                                    movimento salvato
                                */
                                if ($total > 0 && $m != null) {
                                    $m->amount += $total;
                                    $m->save();
                                }

                                return 2;
                            }

                            return 1;
                        },
                        'post' => function (Movement $movement) {
                            $target = $movement->target;
                            if($target != null) {
                                $target->payment_id = $movement->id;
                                $target->save();
                            }
                        },
                        'parse' => function (Movement &$movement, $request) {
                            if ($movement->target_type == 'App\Aggregate') {
                                if (isset($request['delivering-status'])) {
                                    $movement->handling_status = json_decode($request['delivering-status']);
                                }
                            }
                        },
                        'delete' => function(Movement $movement) {
                            $target = $movement->target;
                            if($target != null) {
                                $target->payment_id = 0;
                                $target->save();
                            }
                        }
                    ];

                    break;

                case 'order-payment':
                    $mov->callbacks = [
                        'post' => function (Movement $movement) {
                            $target = $movement->target;
                            $target->payment_id = $movement->id;
                            $target->save();
                        },
                        'delete' => function(Movement $movement) {
                            $target = $movement->target;
                            $target->payment_id = 0;
                            $target->save();
                        }
                    ];

                    break;
            }
        }

        return $types;
    }

    public static function types($identifier = null, $with_trashed = false)
    {
        static $types = null;

        if (is_null($types)) {
            $query = MovementType::orderBy('name', 'asc');
            if ($with_trashed)
                $query = $query->withTrashed();
            $types = self::initSystemTypes($query->get());
        }

        if ($identifier) {
            return $types->where('id', $identifier)->first();
        } else {
            return $types;
        }
    }

    public function hasPayment($type)
    {
        $valid = MovementType::paymentsByType($this->id);
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

        if ($op->operation == 'decrement')
            $amount = $movement->amount * -1;
        else if ($op->operation == 'increment')
            $amount = $movement->amount;
        else
            return;

        $obj->alterBalance($amount, $op->field);
    }

    public function apply($movement)
    {
        $ops = json_decode($this->function);

        foreach($ops as $o) {
            if ($o->method != $movement->method)
                continue;

            foreach($o->sender->operations as $op)
                $this->applyFunction($movement->sender, $movement, $op);

            foreach($o->target->operations as $op)
                $this->applyFunction($movement->target, $movement, $op);

            if (!empty($o->master->operations)) {
                $currentgas = currentAbsoluteGas();

                foreach($o->master->operations as $op)
                    $this->applyFunction($currentgas, $movement, $op);
            }

            break;
        }
    }
}
