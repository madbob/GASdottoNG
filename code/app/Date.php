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

use Auth;
use Log;

class Date extends Model
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
                Log::error('Impossibile recuperare nome del fornitore assegnato alla data ' . $this->id);
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

    /*
        Per motivi ignoti (collisione con qualche altra definizione?), tavolta
        l'attributo dates restituisce una variabile vuota. Meglio cambiare nome
        in all_dates
    */
    public function getAllDatesAttribute()
    {
        if (empty($this->recurring)) {
            return [$this->date];
        }
        else {
            return unrollPeriodic(json_decode($this->recurring));
        }
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

    public function getEndAttribute()
    {
        return $this->internalAttribute('end');
    }

    public function getShippingAttribute()
    {
        return $this->internalAttribute('shipping');
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
}
