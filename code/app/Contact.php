<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class Contact extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
    ];

    public function target()
    {
        return $this->morphTo();
    }

    public function getSlugID()
    {
        return sprintf('%s::%s-%s', $this->target_id, $this->type, str_random(10));
    }

    public function getTypeNameAttribute()
    {
        $types = Contact::types();
        foreach($types as $t)
            if ($t['value'] == $this->type)
                return $t['label'];

        return '???';
    }

    public static function types()
    {
        return [
            [
                'label' => _i('Indirizzo'),
                'value' => 'address'
            ],
            [
                'label' => _i('E-Mail'),
                'value' => 'email'
            ],
            [
                'label' => _i('Telefono'),
                'value' => 'phone'
            ],
            [
                'label' => _i('Cellulare'),
                'value' => 'mobile'
            ],
            [
                'label' => _i('Fax'),
                'value' => 'fax'
            ],
            [
                'label' => _i('Sito Web'),
                'value' => 'website'
            ],
            [
                'label' => _i('Referente'),
                'value' => 'referent'
            ],
        ];
    }
}
