<?php

namespace App;

trait PayableTrait
{
    public function movements()
    {
        return $this->morphMany('App\Movement', 'target');
    }

    public function deleteMovements()
    {
        $this->movements()->delete();
    }
}
