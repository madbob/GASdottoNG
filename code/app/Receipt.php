<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;

/*
    Reminder: non dare per scontato che le fatture abbiano delle prenotazioni
    collegate, può capitare che queste vengano rimosse
*/
class Receipt extends Model implements AccountingDocument
{
    use GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';
    private $cache_value = null;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    public function bookings()
    {
        return $this->belongsToMany('App\Booking');
    }

    public function getSlugID()
    {
        return str_replace('/', '_', $this->number);
    }

    public function getUserAttribute()
    {
        $first = $this->bookings()->first();
        if ($first)
            return $first->user;
        else
            return null;
    }

    public function getNameAttribute()
    {
        $user = $this->user;
        if ($user)
            $user_name = $user->printableName();
        else
            $user_name = '???';

        return sprintf('%s - %s', $user_name, $this->number);
    }

    private function calculateTotal()
    {
        if (empty($this->cache_value)) {
            $this->cache_value['total'] = 0;
            $this->cache_value['total_tax'] = 0;

            foreach($this->bookings as $booking) {
                $book = $booking->delivered_taxed;
                $this->cache_value['total'] += $book[0];
                $this->cache_value['total_tax'] += $book[1];
            }

            $this->cache_value['total'] = round($this->cache_value['total'], 2);
            $this->cache_value['total_tax'] = round($this->cache_value['total_tax'], 2);
        }
    }

    public static function retrieveByAggregateUser($aggregate, $user)
    {
        $bookings_ids = [];

        foreach($aggregate->orders as $order) {
            $booking = $order->userBooking($user);
            if ($booking->exists)
                $bookings_ids[] = $booking->id;
        }

        return Receipt::whereHas('bookings', function($query) use ($bookings_ids) {
            $query->whereIn('booking_id', $bookings_ids);
        })->get();
    }

    public function getTotalAttribute()
    {
        $this->calculateTotal();
        return $this->cache_value['total'];
    }

    public function getTotalTaxAttribute()
    {
        $this->calculateTotal();
        return $this->cache_value['total_tax'];
    }

    /***************************************************** AccountingDocument */

    public function getSortingDateAttribute()
    {
        return $this->date;
    }
}
