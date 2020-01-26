<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\Aggregate;
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
            $grand_total += $booking->getValue('effective', true);
        }

        return $grand_total;
    }

    public function getTotalDeliveredAttribute()
    {
        $grand_total = 0;

        foreach ($this->bookings as $booking) {
            $grand_total += $booking->getValue('delivered', true);
        }

        return $grand_total;
    }

    public function getConvenientStringsAttribute()
    {
        $suppliers = [];
        $shipping_date = PHP_INT_MAX;

        foreach ($this->bookings as $booking) {
            $order = $booking->order;

            $suppliers[$order->supplier->printableName()] = true;

            if ($order->shipping != null && $order->shipping != '0000-00-00') {
                $this_shipping = strtotime($order->shipping);
                if ($this_shipping < $shipping_date) {
                    $shipping_date = $this_shipping;
                }
            }
        }

        $suppliers = array_keys($suppliers);
        sort($suppliers);

        $limit = Aggregate::aggregatesConvenienceLimit();
        if (count($suppliers) > $limit) {
            $suppliers = array_slice($suppliers, 0, $limit);
            $suppliers[] = _i('e altri');
        }

        return [
            'suppliers' => join(', ', $suppliers),
            'shipping' => $shipping_date == PHP_INT_MAX ? _i('indefinita') : strftime('%A %d %B %G', $shipping_date)
        ];
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
        return $this->user->printableName() . $this->headerIcons();
    }
}
