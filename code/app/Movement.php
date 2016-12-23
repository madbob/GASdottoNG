<?php

namespace app;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\GASModel;

class Movement extends Model
{
    use GASModel;

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
        $types = $this->payments();

        foreach ($types as $id => $details) {
            if ($this->method == $id) {
                return $details->icon;
            }
        }

        return 'glyphicon-question-sign';
    }

    public function getTypeMetadataAttribute()
    {
        return self::types($this->type);
    }

    public function getValidPaymentsAttribute()
    {
        $movement_methods = $this->payments();
        $type_metadata = $this->type_metadata;
        $ret = [];

        foreach ($movement_methods as $method_id => $info) {
            if (isset($type_metadata->methods[$method_id])) {
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

        $type_descr = self::types($type);
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

    public static function payments()
    {
        return [
            'cash' => (object) [
                'name' => 'Contanti',
                'identifier' => false,
                'icon' => 'glyphicon-euro',
            ],
            'credit' => (object) [
                'name' => 'Credito Utente',
                'identifier' => false,
                'icon' => 'glyphicon-ok',
            ],
            'bank' => (object) [
                'name' => 'Conto Corrente',
                'identifier' => true,
                'icon' => 'glyphicon-link',
            ],
        ];
    }

    public static function types($identifier = null)
    {
        $ret = [
            'deposit-pay' => (object) [
                'name' => 'Deposito cauzione socio del GAS',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['cash', 'deposits'], $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['bank', 'deposits'], $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $movement->sender->deposit_id = $movement->id;
                        $movement->sender->save();
                    },
                ],
            ],

            'deposit-return' => (object) [
                'name' => 'Restituzione cauzione socio del GAS',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\User',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['cash', 'deposits'], $movement->amount * -1);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['bank', 'deposits'], $movement->amount * -1);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $movement->target->deposit_id = null;
                        $movement->target->save();
                    },
                ],
            ],

            'annual-fee' => (object) [
                'name' => 'Versamento della quota annuale da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'credit' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->balance -= $movement->amount;
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $movement->sender->fee_id = $movement->id;
                        $movement->sender->save();
                    },
                ],
            ],

            'booking-payment' => (object) [
                'name' => 'Pagamento di una prenotazione da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Booking',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->gas->alterBalance(['cash', 'suppliers'], $movement->amount);
                            $movement->target->supplier->balance += $movement->amount;
                        },
                    ],
                    'credit' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->gas->alterBalance('suppliers', $movement->amount);
                            $movement->sender->balance -= $movement->amount;
                            $movement->target->supplier->balance += $movement->amount;
                        },
                    ],
                ],
                'callbacks' => [
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

                                if (isset($handling_status->{$booking->id})) {
                                    $delivered = $handling_status->{$booking->id};
                                } else {
                                    $delivered = $booking->delivered;
                                }

                                if ($total < $delivered) {
                                    $delivered = $total;
                                }

                                $m = $movement->replicate();
                                $m->target_id = $booking->id;
                                $m->target_type = 'App\Booking';
                                $m->amount = $delivered;

                                /*
                                    Qui devo ricaricare la relazione "target", altrimenti resta in memoria quella precedente
                                    (che faceva riferimento ad un Aggregate, dunque non è corretta e sul salvataggio spacca
                                    tutto)
                                */
                                $m->load('target');

                                $m->save();

                                $total -= $delivered;
                                if ($total <= 0) {
                                    break;
                                }
                            }

                            if ($total > 0 && $m != null) {
                                $m->amount += $total;
                                $m->save();
                            }

                            return false;
                        }

                        return true;
                    },
                    'post' => function (Movement $movement) {
                        $movement->target->payment_id = $movement->id;
                        $movement->target->save();
                    },
                    'parse' => function (Movement &$movement, Request $request) {
                        if ($movement->target_type == 'App\Aggregate') {
                            if ($request->has('delivering-status')) {
                                $movement->handling_status = json_decode($request->input('delivering-status'));
                            }
                        }
                    },
                ],
            ],

            'order-payment' => (object) [
                'name' => 'Pagamento dell\'ordine presso il fornitore',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Order',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance(['cash', 'suppliers'], $movement->amount * -1);
                            $movement->target->supplier->balance -= $movement->amount;
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance(['bank', 'suppliers'], $movement->amount * -1);
                            $movement->target->supplier->balance -= $movement->amount;
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $movement->target->payment_id = $movement->id;
                        $movement->target->save();
                    },
                ],
            ],

            'user-credit' => (object) [
                'name' => 'Deposito di credito da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->balance += $movement->amount;
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->balance += $movement->amount;
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'internal-transfer' => (object) [
                'name' => 'Trasferimento interno al GAS, dalla cassa al conto o viceversa',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'generic-get' => (object) [
                'name' => 'Prelievo generico',
                'sender_type' => 'App\Gas',
                'target_type' => null,
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'generic-put' => (object) [
                'name' => 'Versamento generico',
                'sender_type' => 'App\Gas',
                'target_type' => null,
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'supplier-rounding' => (object) [
                'name' => 'Arrotondamento/sconto fornitore',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Supplier',
                'allow_negative' => true,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                            $movement->target->balance += $movement->amount;
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                            $movement->target->balance += $movement->amount;
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],
        ];

        if ($identifier) {
            return $ret[$identifier];
        } else {
            return $ret;
        }
    }
}
