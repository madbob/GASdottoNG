<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Log;

class Date extends Model
{
    use GASModel;

    public function target()
    {
        return $this->morphTo();
    }

    public static function types()
    {
        return [
            [
                'label' => _i('Confermato'),
                'value' => 'confirmed'
            ],
            [
                'label' => _i('Provvisorio'),
                'value' => 'temp'
            ],
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

            return empty($this->description) ? $name : sprintf('%s: %s', $name, $this->description);
        }
    }

    public function getDatesAttribute()
    {
        if (empty($this->recurring))
            return [$this->date];
        else
            return unrollPeriodic(json_decode($this->recurring));
    }

    public function printableName()
    {
        return $this->printableDate('date');
    }

    public function printableHeader()
    {
        return $this->printableDate('date') . ' - Calendario Condiviso - ' . substr($this->description, 0, 100) . '...';
    }
}
