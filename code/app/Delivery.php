<?php

/*
    Questa classe rappresenta un luogo di consegna
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\WithinGas;
use App\Models\Concerns\TracksUpdater;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

class Delivery extends Model
{
    use Cachable, GASModel, HasFactory, ModifiableTrait, SluggableID, TracksUpdater, WithinGas;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'preferred_delivery_id');
    }

    private static function sortByUserName($bookings)
    {
        return $bookings->sortBy(fn ($b, $index) => $b->user->printableName());
    }

    private static function sortByPlace($bookings)
    {
        return $bookings->sortBy([function ($a, $b) {
            $a_place = $a->shipping_place;
            $b_place = $b->shipping_place;

            $ret = 0;

            if (is_null($a_place) && is_null($b_place)) {
                $ret = $a->user->printableName() <=> $b->user->printableName();
            }
            elseif (is_null($a_place)) {
                $ret = -1;
            }
            elseif (is_null($b_place)) {
                $ret = 1;
            }
            else {
                if ($a_place->id != $b_place->id) {
                    $ret = $a_place->name <=> $b_place->name;
                }
                else {
                    $ret = $a->user->printableName() <=> $b->user->printableName();
                }
            }

            return $ret;
        }]);

        return $bookings;
    }

    public static function sortBookingsByShippingPlace($bookings, $shipping_place)
    {
        $bookings = new Collection($bookings);

        if ($shipping_place == 0 || $shipping_place == 'all_by_name') {
            $bookings = self::sortByUserName($bookings);
        }
        elseif ($shipping_place == 'all_by_place') {
            $bookings = self::sortByPlace($bookings);
        }
        else {
            $tmp_bookings = $bookings->filter(fn ($b) => $b->shipping_place && $b->shipping_place->id == $shipping_place);
            $bookings = self::sortByUserName($tmp_bookings);
        }

        return $bookings;
    }
}
