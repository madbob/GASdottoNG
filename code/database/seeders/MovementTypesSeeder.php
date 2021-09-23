<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    private function voidFunctions($array)
    {
        foreach($array as $i => $a) {
            foreach(['sender', 'target', 'master'] as $t) {
                if (!isset($a->$t)) {
                    $array[$i]->$t = (object) [
                        'operations' => []
                    ];
                }
            }
        }

        return $array;
    }

    private function format($ops)
    {
        $ret = (object) [
            'operations' => [],
        ];

        foreach ($ops as $field => $op) {
            $ret->operations[] = (object) [
                'operation' => $op,
                'field' => $field,
            ];
        }

        return $ret;
    }

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
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'cash' => 'increment',
                        'deposits' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'increment',
                        'deposits' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'deposits' => 'increment',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('deposit-return') == null) {
            $type = new MovementType();
            $type->id = 'deposit-return';
            $type->name = 'Restituzione cauzione socio del GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\User';
            $type->fixed_value = null;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'deposits' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                        'deposits' => 'decrement',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('annual-fee') == null) {
            $type = new MovementType();
            $type->id = 'annual-fee';
            $type->name = 'Versamento della quota annuale da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Gas';
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'cash' => 'increment',
                        'gas' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'increment',
                        'gas' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'gas' => 'increment',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('booking-payment') == null) {
            $type = new MovementType();
            $type->id = 'booking-payment';
            $type->name = 'Pagamento prenotazione da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Booking';
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'cash' => 'increment',
                        'suppliers' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'suppliers' => 'increment',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('order-payment') == null) {
            $type = new MovementType();
            $type->id = 'order-payment';
            $type->name = 'Pagamento ordine a fornitore';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\Order';
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'suppliers' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'sender' => $this->format([
                        'bank' => 'decrement',
                        'suppliers' => 'decrement',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('invoice-payment') == null) {
            $type = new MovementType();
            $type->id = 'invoice-payment';
            $type->name = 'Pagamento fattura a fornitore';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\Invoice';
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'suppliers' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'sender' => $this->format([
                        'bank' => 'decrement',
                        'suppliers' => 'decrement',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('user-credit') == null) {
            $type = new MovementType();
            $type->id = 'user-credit';
            $type->name = 'Deposito di credito da parte di un socio';
            $type->sender_type = null;
            $type->target_type = 'App\User';
            $type->fixed_value = null;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'cash' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'bank' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'paypal',
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'paypal' => 'increment',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('booking-payment-adjust') == null) {
            $type = new MovementType();
            $type->id = 'booking-payment-adjust';
            $type->name = 'Aggiustamento pagamento prenotazione da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Booking';
            $type->allow_negative = true;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                    'master' => $this->format([
                        'suppliers' => 'increment',
                    ]),
                ],
            ]));
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
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'master' => $this->format([
                        'cash' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'master' => $this->format([
                        'bank' => 'decrement',
                    ]),
                ]
            ]));
            $type->save();
        }

        if (MovementType::find('generic-put') == null) {
            $type = new MovementType();
            $type->id = 'generic-put';
            $type->name = 'Versamento sul conto';
            $type->sender_type = null;
            $type->target_type = 'App\Gas';
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'increment',
                        'cash' => 'decrement',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('gas-expense') == null) {
            $type = new MovementType();
            $type->id = 'gas-expense';
            $type->name = 'Acquisto/spesa GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = null;
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'gas' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                        'gas' => 'decrement',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('user-refund') == null) {
            $type = new MovementType();
            $type->id = 'user-refund';
            $type->name = 'Rimborso spesa socio';
            $type->sender_type = 'App\Gas';
            $type->target_type = 'App\User';
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'gas' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'gas' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'bank' => 'increment',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('donation-to-gas') == null) {
            $type = new MovementType();
            $type->id = 'donation-to-gas';
            $type->name = 'Donazione al GAS';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Gas';
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'target' => $this->format([
                        'cash' => 'increment',
                        'gas' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'target' => $this->format([
                        'bank' => 'increment',
                        'gas' => 'increment',
                    ]),
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'gas' => 'increment',
                    ]),
                ],
            ]));
            $type->save();
        }

        if (MovementType::find('donation-from-gas') == null) {
            $type = new MovementType();
            $type->id = 'donation-from-gas';
            $type->name = 'Donazione dal GAS';
            $type->sender_type = 'App\Gas';
            $type->target_type = null;
            $type->fixed_value = null;
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'cash' => 'decrement',
                        'gas' => 'decrement',
                    ]),
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                        'gas' => 'decrement',
                    ]),
                ],
            ]));
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
            $type->function = json_encode($this->voidFunctions([
                (object) [
                    'method' => 'cash',
                    'sender' => $this->format([
                        'bank' => 'decrement',
                    ]),
                    'target' => $this->format([
                        'gas' => 'increment',
                        'suppliers' => 'decrement',
                    ]),
                ]
            ]));
            $type->save();
        }
    }
}
