<?php

/*
    Nota bene: questo evento, che attiva il listener DeliverBooking, va usato
    per gestire il flusso interattivo di consegna (consegna e poi pagamento,
    dall'interfaccia web) e non nei casi in cui la consegna viene gestita in
    modo programmatico (e.g. con le consegne veloci)
*/

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

use App\User;
use App\Booking;

class BookingDelivered
{
    use Dispatchable, SerializesModels;

    public $booking;
    public $status;
    public $user;

    public function __construct(Booking $booking, $status, User $user)
    {
        $this->booking = $booking;
        $this->status = $status;
        $this->user = $user;
    }
}
