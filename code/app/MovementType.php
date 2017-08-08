<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

use Auth;

use App\GASModel;

class MovementType extends Model
{
    use SoftDeletes, GASModel, SluggableID;

    public $incrementing = false;

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
                'name' => 'Bonifico',
                'identifier' => true,
                'icon' => 'glyphicon-link',
            ],
        ];
    }

    public static function systemTypes()
    {
        $currentgas = Auth::user()->gas;
        $types = new Collection();

        /*********************************/

        $type = new MovementType();
        $type->id = 'deposit-pay';
        $type->name = 'Deposito cauzione socio del GAS';
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';
        $type->allow_negative = false;
        $type->visibility = false;
        $type->system = true;
        $type->fixed_value = $currentgas->getConfig('deposit_amount');
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'deposits'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'deposits'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ]
                        ]
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'deposits'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->callbacks = [
            'post' => function (Movement $movement) {
                $sender = $movement->sender;
                $sender->deposit_id = $movement->id;
                $sender->save();
            },
        ];

        $types->push($type);

        /*********************************/

        $type = new MovementType();
        $type->id = 'deposit-return';
        $type->name = 'Restituzione cauzione socio del GAS';
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\User';
        $type->allow_negative = false;
        $type->visibility = true;
        $type->system = true;
        $type->fixed_value = $currentgas->getConfig('deposit_amount');
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'deposits'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'deposits'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ]
            ]
        );
        $type->callbacks = [
            'post' => function (Movement $movement) {
                $target = $movement->target;
                $target->deposit_id = null;
                $target->save();
            },
        ];

        $types->push($type);

        /*********************************/

        $type = new MovementType();
        $type->id = 'annual-fee';
        $type->name = 'Versamento della quota annuale da parte di un socio';
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';
        $type->allow_negative = false;
        $type->visibility = false;
        $type->system = true;
        $type->fixed_value = $currentgas->getConfig('annual_fee_amount');
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ]
            ]
        );
        $type->callbacks = [
            'post' => function (Movement $movement) {
                $sender = $movement->sender;
                $sender->fee_id = $movement->id;
                $sender->save();
            },
        ];

        $types->push($type);

        /*********************************/

        $type = new MovementType();
        $type->id = 'booking-payment';
        $type->name = 'Pagamento di una prenotazione da parte di un socio';
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Booking';
        $type->allow_negative = false;
        $type->fixed_value = false;
        $type->visibility = false;
        $type->system = true;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'suppliers'
                            ],
                        ]
                    ],
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'suppliers'
                            ],
                        ]
                    ],
                ]
            ]
        );
        $type->callbacks = [
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

                        $existing_movement = $booking->payment;
                        if ($existing_movement == null) {
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
                $target->payment_id = $movement->id;
                $target->save();
            },
            'parse' => function (Movement &$movement, Request $request) {
                if ($movement->target_type == 'App\Aggregate') {
                    if ($request->has('delivering-status')) {
                        $movement->handling_status = json_decode($request->input('delivering-status'));
                    }
                }
            },
        ];

        $types->push($type);

        /*********************************/

        $type = new MovementType();
        $type->id = 'order-payment';
        $type->name = 'Pagamento dell\'ordine presso il fornitore';
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\Order';
        $type->allow_negative = false;
        $type->visibility = false;
        $type->system = true;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'suppliers'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'suppliers'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ]
            ]
        );
        $type->callbacks = [
            'post' => function (Movement $movement) {
                $target = $movement->target;
                $target->payment_id = $movement->id;
                $target->save();
            },
        ];

        $types->push($type);

        /*********************************/

        $type = new MovementType();
        $type->id = 'supplier-rounding';
        $type->name = 'Arrotondamento/sconto fornitore';
        $type->sender_type = 'App\Supplier';
        $type->target_type = 'App\Gas';
        $type->allow_negative = true;
        $type->visibility = false;
        $type->system = true;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ]
            ]
        );
        $type->callbacks = [];

        $types->push($type);

        return $types;
    }

    public static function types($identifier = null)
    {
        static $types = null;

        if ($types == null) {
            $system_types = self::systemTypes();

            $manual_types = MovementType::all();
            foreach($manual_types as $mt) {
                $mt->visibility = true;
                $mt->system = false;
                $mt->callbacks = [];
            }

            $types = $system_types->merge($manual_types)->sortBy('name');
        }

        if ($identifier) {
            return $types->where('id', $identifier)->first();
        } else {
            return $types;
        }
    }

    private function applyFunction($obj, $movement, $op)
    {
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
                $currentgas = Auth::user()->gas;

                foreach($o->master->operations as $op)
                    $this->applyFunction($currentgas, $movement, $op);
            }
        }
    }
}
