<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        if($this->type == 'internal')
            return $this->description;
        else
            return empty($this->description) ? $this->target->name : sprintf('%s: %s', $this->target->name, $this->description);
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
