<?php

namespace App\Parameters\MovementType;

class BookingPaymentAdjust extends MovementType
{
    public function identifier()
    {
        return 'booking-payment-adjust';
    }

    public function initNew($type)
    {
        $type->name = __('texts.movements.defaults.booking_adjust');
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Booking';
        $type->allow_negative = true;
        $type->visibility = false;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['bank' => 'increment']),
            ],
        ]));

        return $type;
    }
}
