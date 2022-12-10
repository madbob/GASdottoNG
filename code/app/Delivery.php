<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;

use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\WithinGas;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

/*
    Questa classe rappresenta un luogo di consegna
*/

class Delivery extends Model
{
    use HasFactory, ModifiableTrait, GASModel, SluggableID, WithinGas, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'preferred_delivery_id');
    }

    private static function sortByUserName($bookings)
    {
        usort($bookings, function($a, $b) {
            return $a->user->printableName() <=> $b->user->printableName();
        });

        return $bookings;
    }

    private static function sortByPlace($bookings)
    {
        usort($bookings, function($a, $b) {
            $a_place = $a->shipping_place;
            $b_place = $b->shipping_place;

            if (is_null($a_place) && is_null($b_place)) {
                return $a->user->printableName() <=> $b->user->printableName();
            }
            else if (is_null($a_place)) {
                return -1;
            }
            else if (is_null($b_place)) {
                return 1;
            }
            else {
                if ($a_place->id != $b_place->id) {
                    return $a_place->name <=> $b_place->name;
                }
                else {
                    return $a->user->printableName() <=> $b->user->printableName();
                }
            }
        });

        return $bookings;
    }

    public static function sortBookingsByShippingPlace($bookings, $shipping_place)
    {
        if ($shipping_place == 0) {
            // dummy
        }
        else if ($shipping_place == 'all_by_name') {
            $bookings = self::sortByUserName($bookings);
        }
        else if ($shipping_place == 'all_by_place') {
            $bookings = self::sortByPlace($bookings);
        }
        else {
            $tmp_bookings = array_filter($bookings, function($b) use ($shipping_place) {
                return $b->shipping_place && $b->shipping_place->id == $shipping_place;
            });

            $bookings = self::sortByUserName($tmp_bookings);
        }

        return $bookings;
    }
}
