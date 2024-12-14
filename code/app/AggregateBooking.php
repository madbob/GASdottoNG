<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Models\Concerns\TracksUpdater;

/*
    Modello fittizio: non rappresenta nessun dato sul database, ma serve ad
    aggregare tutte le prenotazioni di un utente fatte sugli ordini inclusi in
    un Aggregate
*/

class AggregateBooking extends Model
{
    use GASModel, TracksUpdater;

    public $id;

    public $user;

    public $aggregate;

    public $bookings;

    public function __construct($user_id, $aggregate)
    {
        $this->id = $user_id;
        $this->user = User::withTrashed()->find($user_id);
        $this->aggregate = $aggregate;
        $this->bookings = new Collection();
    }

    public function add($booking)
    {
        $this->bookings->push($booking);
    }

    public function getCreatedAtAttribute()
    {
        $date = '9999';

        foreach ($this->bookings as $booking) {
            if ($booking->created_at < $date) {
                $date = $booking->created_at;
            }
        }

        return $date;
    }

    public function getUpdatedAtAttribute()
    {
        $date = '0000';

        foreach ($this->bookings as $booking) {
            if ($booking->updated_at > $date) {
                $date = $booking->updated_at;
            }
        }

        return $date;
    }

    public function getStatusAttribute()
    {
        /*
            Nota bene: in questo aggregato ci vanno sia le prenotazioni
            effettivamente salvate sul database che le prenotazioni allocate
            ma non realmente esistenti (ma che fungono da wrapper in molte
            circostanze).
            Lo stato dell'aggregato dipende solo da quelle reali: se una
            prenotazioni vera risulta consegnata, ed una "virtuale" no
            (quelle virtuali non lo sono mai, per definizione), comunque tutto
            l'aggregato deve risultare consegnato
        */
        $target = $this->bookings->filter(fn ($b) => $b->exists && $b->status != 'shipped')->first();
        if ($target) {
            return $target->status;
        }

        foreach ($this->bookings as $booking) {
            $target = $booking->friends_bookings->filter(fn ($b) => $b->exists && $b->status != 'shipped')->first();
            if ($target) {
                return $target->status;
            }
        }

        return 'shipped';
    }

    public function getOrderBooking($order)
    {
        foreach ($this->bookings as $booking) {
            if ($booking->order_id == $order->id) {
                return $booking;
            }
        }

        return null;
    }

    public function getValue($type, $with_friends, $force_recalculate = false)
    {
        $grand_total = 0;

        foreach ($this->bookings as $booking) {
            $grand_total += $booking->getValue($type, $with_friends, $force_recalculate);
        }

        return $grand_total;
    }

    public function getDeliveryAttribute()
    {
        foreach ($this->bookings as $booking) {
            if ($booking->delivery) {
                return $booking->delivery;
            }
        }

        return null;
    }

    public function getPaymentAttribute()
    {
        foreach ($this->bookings as $booking) {
            if ($booking->payment) {
                return $booking->payment;
            }
        }

        return null;
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

        $limit = aggregatesConvenienceLimit();
        if (count($suppliers) > $limit) {
            if (filled($this->aggregate->comment)) {
                $suppliers = [$this->aggregate->comment];
            }
            else {
                $suppliers = array_slice($suppliers, 0, $limit);
                $suppliers[] = _i('e altri');
            }
        }

        return [
            'suppliers' => implode(', ', $suppliers),
            'shipping' => $shipping_date == PHP_INT_MAX ? _i('indefinita') : printableDate($shipping_date),
        ];
    }

    public function generateReceipt()
    {
        if ($this->user->gas->hasFeature('extra_invoicing')) {
            $ids = $this->bookings->filter(fn ($b) => $b->exists)->pluck('id')->all();

            if (empty($ids)) {
                \Log::error('Tentativo di creare fattura non assegnata a nessuna prenotazione');

                return;
            }

            $receipt = Receipt::whereHas('bookings', function ($query) use ($ids) {
                $query->whereIn('bookings.id', $ids);
            })->first();

            if ($receipt == null) {
                $receipt = new Receipt();
                $receipt->number = $this->user->gas->nextInvoiceNumber();
            }

            $receipt->date = date('Y-m-d');
            $receipt->save();
            $receipt->bookings()->sync($ids);
        }
    }

    public function printableHeader()
    {
        return $this->user->printableName() . $this->headerIcons();
    }

    /********************************************************** TracksUpdater */

    public function getPrintableUpdaterAttribute()
    {
        $last_update = null;
        $last_updater = null;

        foreach ($this->bookings->filter(fn ($b) => $b->updater) as $booking) {
            if (is_null($last_update) || $booking->updated_at->greaterThan($last_update)) {
                $last_update = $booking->updated_at;
                $last_updater = $booking->updater;
            }
        }

        if ($last_updater) {
            return _i('Ultima Modifica: <br class="d-block d-md-none">%s - %s', [$last_update->format('d/m/Y'), $last_updater->printableName()]);
        }
        else {
            return '';
        }
    }
}
