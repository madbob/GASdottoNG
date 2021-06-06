<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    public function run()
    {
        /*
            Questi sono i tipi di movimento il cui comportamento è definito nel
            codice: non cambiare gli identificativi a sproposito!
        */

        if (MovementType::find('deposit-pay') == null) {
            $type = new MovementType();
            $type->id = 'deposit-pay';
            $type->name = 'Deposito cauzione socio del GAS';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Gas';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
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
            $type->save();
        }

        if (MovementType::find('deposit-return') == null) {
            $type = new MovementType();
            $type->id = 'deposit-return';
            $type->name = 'Restituzione cauzione socio del GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\User';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = true;
            $type->system = true;
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
            $type->save();
        }

        if (MovementType::find('annual-fee') == null) {
            $type = new MovementType();
            $type->id = 'annual-fee';
            $type->name = 'Versamento della quota annuale da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Gas';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
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
            $type->save();
        }

        if (MovementType::find('booking-payment') == null) {
            $type = new MovementType();
            $type->id = 'booking-payment';
            $type->name = 'Pagamento prenotazione da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Booking';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
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
                    ],
                ]
            );
            $type->save();
        }

        if (MovementType::find('order-payment') == null) {
            $type = new MovementType();
            $type->id = 'order-payment';
            $type->name = 'Pagamento ordine a fornitore';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\Order';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
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
            $type->save();
        }

        if (MovementType::find('invoice-payment') == null) {
            $type = new MovementType();
            $type->id = 'invoice-payment';
            $type->name = 'Pagamento fattura a fornitore';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\Invoice';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
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
            $type->save();
        }

        if (MovementType::find('user-credit') == null) {
            $type = new MovementType();
            $type->id = 'user-credit';
            $type->name = 'Deposito di credito da parte di un socio';
            $type->sender_type = null;
            $type->target_type = 'App\User';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->system = true;
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
                            ]
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
                            ]
                        ],
                        'master' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'increment',
                                    'field' => 'bank'
                                ],
                            ]
                        ]
                    ],
                    (object) [
                        'method' => 'paypal',
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
                                    'field' => 'paypal'
                                ],
                            ]
                        ]
                    ]
                ]
            );
            $type->save();
        }

        /*
            Il comportamento di questi movimenti non è strettamente vincolati al
            codice, ma si consiglia comunque di non modificarli se non molto
            oculatamente
        */

        if (MovementType::find('user-decredit') == null) {
            $type = new MovementType();
            $type->id = 'user-decredit';
            $type->name = 'Reso credito per un socio';
            $type->sender_type = 'App\User';
            $type->target_type = null;
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->function = json_encode(
                [
                    (object) [
                        'method' => 'cash',
                        'sender' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'decrement',
                                    'field' => 'bank'
                                ],
                            ]
                        ],
                        'target' => (object) [
                            'operations' => []
                        ],
                        'master' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'decrement',
                                    'field' => 'cash'
                                ],
                            ]
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
                            ]
                        ],
                        'target' => (object) [
                            'operations' => []
                        ],
                        'master' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'decrement',
                                    'field' => 'bank'
                                ],
                            ]
                        ]
                    ]
                ]
            );
            $type->save();
        }

        if (MovementType::find('generic-put') == null) {
            $type = new MovementType();
            $type->id = 'generic-put';
            $type->name = 'Versamento sul conto';
            $type->sender_type = null;
            $type->target_type = 'App\Gas';
            $type->allow_negative = false;
            $type->fixed_value = null;
            $type->function = json_encode(
                [
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
                                    'operation' => 'decrement',
                                    'field' => 'cash'
                                ],
                            ]
                        ],
                        'master' => (object) [
                            'operations' => []
                        ]
                    ],
                ]
            );
            $type->save();
        }

        if (MovementType::find('gas-expense') == null) {
            $type = new MovementType();
            $type->id = 'gas-expense';
            $type->name = 'Acquisto/spesa GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = null;
            $type->allow_negative = false;
            $type->fixed_value = null;
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
                                    'field' => 'gas'
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
                                    'field' => 'gas'
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
                ]
            );
            $type->save();
        }

        if (MovementType::find('user-refund') == null) {
            $type = new MovementType();
            $type->id = 'user-refund';
            $type->name = 'Rimborso spesa socio';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\User';
            $type->allow_negative = false;
            $type->fixed_value = null;
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
                                    'field' => 'gas'
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
                        'method' => 'credit',
                        'sender' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'decrement',
                                    'field' => 'gas'
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
                            'operations' => []
                        ]
                    ],
                ]
            );
            $type->save();
        }

        if (MovementType::find('donation-to-gas') == null) {
            $type = new MovementType();
            $type->id = 'donation-to-gas';
            $type->name = 'Donazione al GAS';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Gas';
            $type->allow_negative = false;
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
                    ],
                ]
            );
            $type->save();
        }

        if (MovementType::find('donation-from-gas') == null) {
            $type = new MovementType();
            $type->id = 'donation-from-gas';
            $type->name = 'Donazione dal GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = null;
            $type->allow_negative = false;
            $type->fixed_value = null;
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
                                    'field' => 'gas'
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
                                    'field' => 'gas'
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
                ]
            );
            $type->save();
        }

        if (MovementType::find('supplier-rounding') == null) {
            $type = new MovementType();
            $type->id = 'supplier-rounding';
            $type->name = 'Arrotondamento/sconto fornitore';
            $type->sender_type = 'App\Supplier';
            $type->target_type = 'App\Gas';
            $type->allow_negative = true;
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
            $type->save();
        }
    }
}
