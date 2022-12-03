<?php

namespace App\Models\Concerns;

use App\ModifiedValue;

trait ModifiedTrait
{
    public function modifiedValues()
    {
        return $this->morphMany(ModifiedValue::class, 'target');
    }

    public abstract function getModifiedRelations();
}
