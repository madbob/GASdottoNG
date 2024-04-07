<?php

/*
    Il modello Date viene usato per diverse cose, in modo più o meno esplicito.

    type == 'confirmed' o type == 'temp': è usato per le date gestite in
    "Gestisci Date", nella pagina degli Ordini. In questo caso sono semplici
    date che appaiono sul calendario per rappresentare, idealmente, degli ordini
    futuri. Non ha alcuna funzione attiva.

    type == 'internal': viene usato per aggiungere elementi arbitrari nel
    calendario, ed è maneggiato insieme alle Notification. Anche in questo caso
    non ha alcuna funzione attiva.

    type == 'order': viene usato per la gestione degli Ordini Automatici. Viene
    amministrato come le date del primo caso, da DatesService, ma questi
    elementi sono maneggiati dal comando OpenOrders per aprire in modo
    automatico gli ordini.
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use App\Models\Concerns\Datable;

class Date extends Model implements Datable
{
    use GASModel;

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public static function types()
    {
        return [
            'confirmed' => _i('Confermato'),
            'temp' => _i('Provvisorio'),
        ];
    }

    public function scopeLocalGas($query)
    {
        $user = Auth::user();

        $query->where(function($query) use ($user) {
            $query->where('target_type', 'App\GAS')->where('target_id', $user->gas->id);
        })->orWhere(function($query) {
            $query->where('target_type', 'App\Supplier')->whereIn('target_id', Supplier::get('id')->toArray());
        });
    }

    public function getCalendarStringAttribute()
    {
        if($this->type == 'internal') {
            return $this->description;
        }
        else {
            $target = $this->target;
            if ($target) {
                $name = $this->target->name;
            }
            else {
                \Log::error('Impossibile recuperare nome del fornitore assegnato alla data ' . $this->id);
                $name = '???';
            }

            if ($this->type == 'order') {
                return $name;
            }
            else {
                return empty($this->description) ? $name : sprintf('%s: %s', $name, $this->description);
            }
        }
    }

    public function getAllDatesAttribute()
    {
        if (empty($this->recurring)) {
            return [$this->date];
        }
        else {
            $dates = unrollPeriodic(json_decode($this->recurring));

            if ($this->type == 'order' && $this->action != 'open') {
                $offset = $this->first_offset;
                $shifted = [];

                foreach($dates as $date) {
                    $shifted[] = Carbon::parse($date)->subDays($offset)->format('Y-m-d');
                }

                $dates = $shifted;
            }

            return $dates;
        }
    }

    public function getOrderDatesAttribute()
    {
        $ret = [];

        if ($this->type != 'order') {
            return $ret;
        }

        $dates = unrollPeriodic(json_decode($this->recurring));
        $action = $this->action;
        $offset1 = $this->first_offset;
        $offset2 = $this->second_offset;

        foreach($dates as $date) {
            $d = Carbon::parse($date);
            $node = null;

            switch($action) {
                case 'open':
                    $node = (object) [
                        'start' => $d->format('Y-m-d'),
                        'end' => $d->copy()->addDays($offset1)->format('Y-m-d'),
                        'shipping' => $d->copy()->addDays($offset2)->format('Y-m-d'),
                    ];

                    break;

                case 'close':
                    $node = (object) [
                        'start' => $d->copy()->subDays($offset1)->format('Y-m-d'),
                        'end' => $d->format('Y-m-d'),
                        'shipping' => $d->copy()->addDays($offset2)->format('Y-m-d'),
                    ];

                    break;

                case 'ship':
                    $node = (object) [
                        'start' => $d->copy()->subDays($offset1)->format('Y-m-d'),
                        'end' => $d->copy()->subDays($offset2)->format('Y-m-d'),
                        'shipping' => $d->format('Y-m-d'),
                    ];

                    break;
            }

            if ($node) {
                $node->target = $this->target;
                $node->comment = $this->comment;
                $ret[] = $node;
            }
        }

        return $ret;
    }

    public function printableName()
    {
        return $this->printableDate('date');
    }

    public function printableHeader()
    {
        return $this->printableDate('date') . ' - Calendario Condiviso - ' . substr($this->description, 0, 100) . '...';
    }

    private function internalAttribute($name)
    {
        if ($this->type != 'order') {
            return '';
        }

        $attributes = json_decode($this->description);
        return $attributes->$name ?? '';
    }

    public function getActionAttribute()
    {
        return $this->internalAttribute('action');
    }

    public function getFirstOffsetAttribute()
    {
        return $this->internalAttribute('offset1');
    }

    public function getSecondOffsetAttribute()
    {
        return $this->internalAttribute('offset2');
    }

    public function getCommentAttribute()
    {
        return $this->internalAttribute('comment');
    }

    public function getSuspendAttribute()
    {
        return $this->internalAttribute('suspend') == 'true';
    }

    public function updateRecurringToDate($last_date)
    {
        $dates = $this->all_dates;

        foreach($dates as $read_date) {
            if ($read_date > $last_date) {
                $data = json_decode($this->recurring);
                $data->from = $read_date;
                $this->recurring = json_encode($data);
                $this->save();
                return true;
            }
        }

        $this->delete();
        return false;
    }

    /**************************************************************** Datable */

    public function getSortingDateAttribute()
    {
        return $this->date;
    }
}
