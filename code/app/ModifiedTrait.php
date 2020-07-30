<?php

namespace App;

use Auth;

trait ModifiedTrait
{
    public function modifiedValues()
    {
        return $this->morphMany('App\ModifiedValue', 'target');
    }

    public abstract function getModifiedRelations();
}
