<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Concerns\TracksUpdater;
use App\Events\SluggableCreating;

class Group extends Model
{
    use GASModel, SluggableID, TracksUpdater;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function circles(): HasMany
    {
        return $this->hasMany(Circle::class);
    }

    private static function sortByUserName($bookings)
    {
        usort($bookings, function($a, $b) {
            return $a->user->printableName() <=> $b->user->printableName();
        });

        return $bookings;
    }

    private static function sortByCircle($bookings)
    {
        usort($bookings, function($a, $b) {
            return $a->circles_sorting <=> $b->circles_sorting;
        });

        return $bookings;
    }

    public static function sortBookings($bookings, $circles)
    {
        $actual_circles = Circle::whereIn('id', $circles)->with('group')->get();

        if ($actual_circles->isEmpty()) {
            $tmp_bookings = $bookings;
        }
        else {
            $tmp_bookings = [];

            foreach($bookings as $booking) {
                $mycircles = $booking->involvedCircles();
                $valid = true;

                foreach($circles as $required_circle) {
                    if (is_null($mycircles->firstWhere('id', $required_circle->id))) {
                        $valid = false;
                        break;
                    }
                }

                if ($valid) {
                    $tmp_bookings[] = $booking;
                }
            }
        }

        if (in_array('all_by_name', $circles)) {
            $bookings = self::sortByUserName($tmp_bookings);
        }
        else {
            $bookings = self::sortByCircle($tmp_bookings);
        }

        return $bookings;
    }
}
