<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Concerns\Datable;
use App\Events\SluggableCreating;

/*
    Reminder: non dare per scontato che le fatture abbiano delle prenotazioni
    collegate, può capitare che queste vengano rimosse
*/
class Receipt extends Model implements Datable
{
    use GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany('App\Booking');
    }

    public function getSlugID()
    {
        return str_replace('/', '_', $this->number);
    }

    public function getUserAttribute()
    {
        $first = $this->bookings->first();
        if ($first)
            return $first->user;
        else
            return null;
    }

    public function getNameAttribute()
    {
        $user = $this->user;
        if ($user) {
            $user_name = $user->printableName();
        }
        else {
            $user_name = '???';
        }

        return sprintf('%s - %s - %s', $user_name, printableDate($this->date), $this->number);
    }

    private function calculateTotal()
    {
        return $this->innerCache('totals', function($obj) {
            $data = [
                'total' => 0,
                'total_tax' => 0,
                'others' => 0,
            ];

            foreach($obj->bookings as $booking) {
                $book = $booking->delivered_taxed;
                $data['total'] += $book[0];
                $data['total_tax'] += $book[1];

                foreach($booking->aggregatedModifiers() as $am) {
                    $data['others'] += $am->amount;
                }
            }

            $data['total'] = round($data['total'], 2);
            $data['total_tax'] = round($data['total_tax'], 2);

            return $data;
        });
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
        $data = $this->calculateTotal();
        return $data['total'];
    }

    public function getTotalTaxAttribute()
    {
        $data = $this->calculateTotal();
        return $data['total_tax'];
    }

    public function getTotalOtherAttribute()
    {
        $data = $this->calculateTotal();
        return $data['others'];
    }

    /**************************************************************** Datable */

    public function getSortingDateAttribute()
    {
        return $this->date;
    }
}
