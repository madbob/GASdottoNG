<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Spatie\Feed\FeedItem;
use Spatie\Feed\Feedable;

use Auth;
use URL;
use Log;

use App\GASModel;
use App\AggregateBooking;
use App\Events\AttachableToGas;

class Aggregate extends Model implements Feedable
{
    use GASModel;

    protected $dispatchesEvents = [
        'created' => AttachableToGas::class
    ];

    protected static function boot()
    {
        parent::boot();

        $user = Auth::user();
        if ($user != null) {
            $gas_id = $user->gas->id;

            static::addGlobalScope('gas', function (Builder $builder) use ($gas_id) {
                $builder->whereHas('gas', function($query) use ($gas_id) {
                    $query->where('gas_id', $gas_id);
                });
            });
        }
    }

    public function gas()
    {
        return $this->belongsToMany('App\Gas');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->with('supplier')->with('products')->orderBy('end', 'desc');
    }

    public function scopeSupplier($query, $supplier_id)
    {
        $query->whereHas('orders', function ($query) use ($supplier_id) {
            $query->where('supplier_id', '=', $supplier_id);
        });
    }

    public static function easyFilter($supplier, $startdate, $enddate, $statuses = null)
    {
        if (is_object($supplier))
            $supplier_id = $supplier->id;
        else
            $supplier_id = $supplier;

        if ($statuses == null)
            $statuses = ['open', 'closed', 'shipped', 'suspended', 'archived'];

        /*
            Questa funzione dovrebbe prendere in considerazione anche i permessi
            dell'utente corrente, e tornare solo gli aggregati che contengono
            ordini tipo:
            $user->can('supplier.orders', $order->supplier) || $user->can('supplier.shippings', $order->supplier)
        */

        $orders = self::with('orders')->whereHas('orders', function ($query) use ($supplier_id, $startdate, $enddate, $statuses) {
            if (!empty($supplier_id))
                $query->where('supplier_id', '=', $supplier_id);
            $query->where('start', '>=', $startdate)->where('end', '<=', $enddate)->whereIn('status', $statuses);
        })->get();

        $orders->sort(function($a, $b) {
            return strcmp($a->shipping, $b->shipping);
        });

        return $orders;
    }

    public function getStatusAttribute()
    {
        $priority = ['suspended', 'open', 'closed', 'shipped', 'archived'];
        $index = 10;

        foreach ($this->orders as $order) {
            $a = array_search($order->status, $priority);
            if ($a < $index) {
                $index = $a;
            }
        }

        if ($index == 10) {
            Log::debug('Impossibile recuperare stato aggregato ' . $this->id . ' con ' . $this->orders->count() . ' ordini');
            $index = 2;
        }

        return $priority[$index];
    }

    public static function getByStatus($status, $inverse = false)
    {
        $operator = '=';
        if ($inverse) {
            $operator = '!=';
        }

        return self::whereHas('orders', function ($query) use ($status, $operator) {
            $query->where('status', $operator, $status);
        })->get();
    }

    /*
        Aggregando molti ordini insieme, alcune composizioni grafiche nella
        visualizzazione degli aggregati diventano sostanzialmente illeggibili.
        Questa funzione ritorna un numero ragionevole di ordini entro cui si
        possono comporre stringhe e contenuti, superato il quale è consigliato
        adottare un'altra strategia
    */
    public static function aggregatesConvenienceLimit()
    {
        return 3;
    }

    private function computeStrings()
    {
        $names = [];
        $dates = [];

        $orders = $this->orders;

        if ($orders->count() > Aggregate::aggregatesConvenienceLimit()) {
            $start_date = PHP_INT_MAX;
            $end_date = 0;
            $shipping_date = 0;

            foreach ($orders as $order) {
                $names[] = $order->printableName();

                $this_start = strtotime($order->start);
                if ($this_start < $start_date)
                    $start_date = $this_start;

                $this_end = strtotime($order->end);
                if ($this_end > $end_date)
                    $end_date = $this_end;

                if ($this->shipping != null && $this->shipping != '0000-00-00') {
                    $this_shipping = strtotime($order->shipping);
                    if ($this_shipping > $shipping_date)
                        $shipping_date = $this_shipping;
                }
            }

            $date_string = sprintf('da %s a %s', strftime('%A %d %B %G', $start_date), strftime('%A %d %B %G', $end_date));
            if ($shipping_date != 0)
                $date_string .= sprintf(', in consegna %s', strftime('%A %d %B %G', $shipping_date));
            $dates[] = $date_string;
        }
        else {
            foreach ($orders as $order) {
                $names[] = $order->printableName();
                $dates[] = $order->printableDates();
            }
        }

        return [implode(' | ', $names), implode(' / ', $dates)];
    }

    public function printableName()
    {
        $name = $this->comment;
        if (!empty($name))
            $name .= ': ';

        $name .= $this->innerCache('names', function($obj) {
            list($name, $date) = $this->computeStrings();
            $this->setInnerCache('dates', $date);
            return $name;
        });

        return $name;
    }

    public function printableDates()
    {
        return $this->innerCache('dates', function($obj) {
            list($name, $date) = $this->computeStrings();
            $this->setInnerCache('names', $name);
            return $date;
        });
    }

    public function printableHeader()
    {
        $ret = $this->printableName();
        $icons = $this->icons();

        if (!empty($icons)) {
            $ret .= '<div class="pull-right">';

            foreach ($icons as $i) {
                $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
            }

            $ret .= '</div>';
        }

        $ret .= sprintf('<br/><small>%s</small>', $this->printableDates());

        return $ret;
    }

    public function printableUserHeader()
    {
        $ret = $this->printableHeader();

        $user = Auth::user();
        $tot = 0;
        $friends_tot = 0;

        foreach($this->orders as $o) {
            $b = $o->userBooking($user->id);
            $tot += $b->total_value;
            $friends_tot += $b->total_friends_value;
        }

        if($tot == 0 && $friends_tot == 0) {
            $message = _i("Non hai partecipato a quest'ordine");
        }
        else {
            if ($friends_tot == 0)
                $message = _i('Hai ordinato %s', printablePriceCurrency($tot));
            else
                $message = _i('Hai ordinato %s + %s', [printablePriceCurrency($tot), printablePriceCurrency($friends_tot)]);
        }

        $ret .= '<span class="pull-right">' . $message . '</span>';
        return $ret;
    }

    public function getBookingURL()
    {
        return URL::action('BookingController@index').'#' . $this->id;
    }

    public function isActive()
    {
        foreach ($this->orders as $order) {
            if ($order->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function isRunning()
    {
        foreach ($this->orders as $order) {
            if ($order->isRunning()) {
                return true;
            }
        }

        return false;
    }

    public function canShip()
    {
        $user = Auth::user();

        foreach ($this->orders as $order) {
            if ($user->can('supplier.shippings', $order->supplier)) {
                return true;
            }
        }

        return false;
    }

    public function getBookingsAttribute()
    {
        $ret = [];

        foreach ($this->orders as $order) {
            foreach ($order->topLevelBookings() as $booking) {
                $booking->setRelation('order', $order);
                $user_id = $booking->user->id;

                if (!isset($ret[$user_id])) {
                    $ret[$user_id] = new AggregateBooking($user_id);
                }

                $ret[$user_id]->add($booking);
            }

            /*
                Dopo aver raccolto le prenotazioni degli utenti principali,
                ripesco anche quelle degli utenti "amici" il cui utente
                principale non ha effettuato prenotazioni.
                In tal caso creo una prenotazione anche per l'utente
                principale, lasciandola vuota, in modo che sia comunque
                possibile accedere successivamente alle sotto-prenotazioni ed
                assegnare il movimento di pagamento
            */
            $collected_users = array_keys($ret);
            $recovered_master_users = [];

            $bookings_by_friends = $order->bookings()->whereHas('user', function($query) use ($collected_users) {
                $query->whereNotNull('parent_id')->whereNotIn('parent_id', $collected_users);
            })->get();

            foreach($bookings_by_friends as $booking) {
                $user_id = $booking->user->parent_id;

                if (isset($recovered_master_users[$user_id])) {
                    continue;
                }

                if (!isset($ret[$user_id])) {
                    $ret[$user_id] = new AggregateBooking($user_id);
                }

                $fake_booking = $order->userBooking($user_id);
                $fake_booking->status = $booking->status;
                $fake_booking->save();
                $ret[$user_id]->add($fake_booking);
                $recovered_master_users[$user_id] = true;
            }
        }

        uasort($ret, function($a, $b) {
            $a_status = $a->status;
            $b_status = $b->status;

            if ($a_status == $b_status) {
                return strcmp($a->user->printableName(), $b->user->printableName());
            }
            else {
                if ($a_status == 'pending')
                    return -1;
                if ($b_status == 'pending')
                    return 1;
                if ($a_status == 'saved')
                    return -1;
                if ($b_status == 'saved')
                    return 1;

                return -1;
            }
        });

        return $ret;
    }

    public function getLastNotifyAttribute()
    {
        return $this->innerCache('last_notify', function($obj) {
            return $obj->orders()->first()->last_notify;
        });
    }

    public function getSupplierNameAttribute()
    {
        return $this->innerCache('supplier_name', function($obj) {
            return $obj->orders()->first()->supplier->name;
        });
    }

    public function getStartAttribute()
    {
        return $this->innerCache('start', function($obj) {
            return $obj->orders()->min('start');
        });
    }

    public function getEndAttribute()
    {
        return $this->innerCache('end', function($obj) {
            return $obj->orders()->max('end');
        });
    }

    public function getShippingAttribute()
    {
        return $this->innerCache('shipping', function($obj) {
            return $obj->orders()->min('shipping');
        });
    }

    public function bookingBy($user_id)
    {
        $ret = new AggregateBooking($user_id);

        foreach ($this->orders as $order) {
            $booking = $order->userBooking($user_id);
            $ret->add($booking);
        }

        return $ret;
    }

    public function getPermissionsProxies()
    {
        $suppliers = [];

        foreach($this->orders as $order)
            $suppliers[] = $order->supplier;

        return $suppliers;
    }

    /*************************************************************** Feedable */

    public function toFeedItem()
    {
        $summary = '';

        foreach($this->orders as $order) {
            $summary .= $order->printableName() . "<br>\n";
            foreach($order->products as $product)
                $summary .= $product->printableName() . "<br>\n";
            $summary .= "<br>\n";
        }

        return FeedItem::create()
            ->id($this->id)
            ->title($this->printableName())
            ->summary($summary)
            ->updated($this->updated_at)
            ->link($this->getBookingURL())
            ->author($this->gas->first()->printableName());
    }

    public static function getFeedItems()
    {
        return Aggregate::getByStatus('open');
    }
}
