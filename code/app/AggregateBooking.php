<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\User;

/*
    Modello fittizio: non rappresenta nessun dato sul database, ma serve ad
    aggregare tutte le prenotazioni di un utente fatte sugli ordini inclusi in
    un Aggregate
*/

class AggregateBooking extends Model
{
    use GASModel;

    public $id;
    public $user;
    public $bookings = [];

    public function __construct($user_id)
    {
        $this->id = $user_id;
        $this->user = User::withTrashed()->find($user_id);
    }

    public function add($booking)
    {
        $this->bookings[] = $booking;
    }

    public function getStatusAttribute()
    {
        foreach ($this->bookings as $booking) {
            /*
                Nota bene: in questo aggregato ci vanno sia le prenotazioni
                effettivamente salvate sul database che le prenotazioni allocate
                ma non realmente esistenti (ma che fungono da wrapper in molte
                circostanze).
                Lo stato dell'aggregato dipende solo da quelle reali: se una
                prenotazioni vera risulta consegnata, ed una "virtuale" no
                (quelle virtuali non lo sono mai, per definizione), comunque
                tutto l'aggregato deve risultare consegnato
            */
            if ($booking->exists && $booking->status != 'shipped') {
                return $booking->status;
            }
        }

        foreach ($this->bookings as $booking) {
            foreach($booking->friends_bookings as $fbooking) {
                if ($fbooking->exists && $fbooking->status != 'shipped') {
                    return $fbooking->status;
                }
            }
        }

        return 'shipped';
    }

    public function getTotalValueAttribute()
    {
        $grand_total = 0;

        foreach ($this->bookings as $booking) {
            $grand_total += $booking->total_value;
            $grand_total += $booking->total_friends_value;
        }

        return $grand_total;
    }

    public function getTotalDeliveredAttribute()
    {
        $grand_total = 0;

        foreach ($this->bookings as $booking)
            $grand_total += $booking->total_delivered;

        return $grand_total;
    }

    public function generateReceipt()
    {
        if ($this->user->gas->hasFeature('extra_invoicing')) {
            $ids = [];
            foreach ($this->bookings as $booking)
                $ids[] = $booking->id;

            if (empty($ids)) {
                Log::error('Tentativo di creare fattura non assegnata a nessuna prenotazione');
                return;
            }

            $receipt = Receipt::whereHas('bookings', function($query) use ($ids) {
                $query->whereIn('bookings.id', $ids);
            })->first();

            if ($receipt == null) {
                $receipt = new Receipt();
                $receipt->number = $this->user->gas->nextInvoiceNumber();
                $receipt->date = date('Y-m-d');
                $receipt->save();
                $receipt->bookings()->sync($ids);
            }
        }
    }

    public function printableHeader()
    {
        $ret = $this->user->printableName();
        $icons = $this->icons();

        if (!empty($icons)) {
            $ret .= '<div class="pull-right">';

            foreach ($icons as $i) {
                $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
            }

            $ret .= '</div>';
        }

        return $ret;
    }
}
