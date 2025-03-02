<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Events\SluggableCreating;

class VariantValue extends Model
{
    use Cachable, GASModel, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function getSlugID()
    {
        /*
            Gli ID dei valori delle varianti finiscono spesso con l'essere
            lunghissimi, essendo la concatenzazione dell'ID fornitore, dell'ID
            prodotto, dell'ID variante e del valore stesso.
            Qui mitigo questa lunghezza tagliando i primi 50 caratteri del nome,
            e faccio altrettanto col nome delle varianti, confidando nel fatto
            che SlugModel provvederà poi a rendere univoci eventuali valori
            coincidenti
        */
        return sprintf('%s::%s', $this->variant_id, Str::slug(substr($this->value, 0, 50)));
    }

    /*************************************************************** GASModel */

    public function printableName()
    {
        return $this->value;
    }
}
