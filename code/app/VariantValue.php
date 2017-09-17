<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class VariantValue extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
    ];

    public function variant()
    {
        return $this->belongsTo('App\Variant');
    }

    public function printableFullValue()
    {
        if ($this->variant->has_offset) {
            return sprintf('%s (%s%.02f€)', $this->value, ($this->price_offset > 0 ? '+' : '-'), abs($this->price_offset));
        } else {
            return $this->value;
        }
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->variant_id, str_slug($this->value));
    }
}
