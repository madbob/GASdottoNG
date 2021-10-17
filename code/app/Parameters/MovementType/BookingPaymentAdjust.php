<?php

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;

class BookingPaymentAdjust extends MovementType
{
    public function identifier()
    {
        return 'booking-payment-adjust';
    }

    public function create()
    {
        $type = new MovementTypeModel();

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
}
