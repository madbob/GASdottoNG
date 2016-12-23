<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\User;

/*
        Modello fittizio: non rappresenta nessun dato sul database, ma serve
        ad aggregare tutte le prenotazioni di un utente fatte sugli ordini
        inclusi in un Aggregate
*/

class AggregateBooking extends Model
{
    use GASModel;

    public $id;

    private $user;
    private $bookings = [];

    public function __construct($user_id)
    {
        $this->id = $user_id;
        $this->user = User::find($user_id);
    }

    public function add($booking)
    {
        $this->bookings[] = $booking;
    }

    public function getStatusAttribute()
    {
        foreach ($this->bookings as $booking) {
            if ($booking->status != 'shipped') {
                return $booking->status;
            }
        }

        return 'shipped';
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
