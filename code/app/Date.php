<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

use App\GASModel;

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

    public function printableName()
    {
        return $this->printableDate('date');
    }

    public function printableHeader()
    {
        return $this->printableDate('date') . ' - Calendario Condiviso - ' . substr($this->description, 0, 100) . '...';
    }
}
