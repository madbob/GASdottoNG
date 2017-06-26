<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;

class Contact extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    public function target()
    {
        return $this->morphsTo();
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
                'label' => 'Indirizzo',
                'value' => 'address'
            ],
            [
                'label' => 'E-Mail',
                'value' => 'email'
            ],
            [
                'label' => 'Telefono',
                'value' => 'phone'
            ],
            [
                'label' => 'Cellulare',
                'value' => 'mobile'
            ],
            [
                'label' => 'Fax',
                'value' => 'fax'
            ],
            [
                'label' => 'Sito Web',
                'value' => 'website'
            ],
        ];
    }
}
