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
            'address' => __('texts.generic.address'),
            'referent' => __('texts.supplier.referent'),
            'email' => __('texts.generic.email'),
            'skip_email' => __('texts.generic.email_no_notifications'),
            'phone' => __('texts.generic.phone'),
            'mobile' => __('texts.generic.cellphone'),
            'fax' => __('texts.generic.fax'),
            'website' => __('texts.generic.website'),
        ];

        if (currentAbsoluteGas()->hasFeature('integralces')) {
            $ret['integralces'] = 'IntegralCES';
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
