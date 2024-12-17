<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\ModifiedValue;

trait ModifiedTrait
{
    public function modifiedValues(): MorphMany
    {
        return $this->morphMany(ModifiedValue::class, 'target')->with(['modifier', 'modifier.modifierType']);
    }

    abstract public function getModifiedRelations();
}
