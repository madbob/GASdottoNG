<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

use App\Events\SluggableCreating;

class Contact extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSlugID()
    {
        return sprintf('%s::%s-%s', $this->target_id, $this->type, Str::random(10));
    }

    public function getTypeNameAttribute()
    {
        $types = Contact::types();

        return $types[$this->type] ?? '???';
    }

    public static function types()
    {
        $ret = [
            'address' => __('generic.address'),
            'referent' => _i('Referente'),
            'email' => __('generic.email'),
            'skip_email' => _i('E-Mail (no notifiche)'),
            'phone' => __('generic.phone'),
            'mobile' => _i('Cellulare'),
            'fax' => _i('Fax'),
            'website' => _i('Sito Web'),
        ];

        if (currentAbsoluteGas()->hasFeature('integralces')) {
            $ret['integralces'] = _i('IntegralCES');
        }

        return $ret;
    }

    public function asAddress()
    {
        $tokens = explode(',', $this->value);
        foreach ($tokens as $index => $value) {
            $tokens[$index] = trim($value);
        }

        for ($i = count($tokens); $i < 3; $i++) {
            $tokens[$i] = '';
        }

        return $tokens;
    }

    /*************************************************************** GASModel */

    public function printableName()
    {
        return $this->value;
    }
}
