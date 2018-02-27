<?php

namespace App\Singletons;

class MovementsHub
{
    private $recalculating = false;

    public function setRecalculating($active)
    {
        $this->recalculating = $active;
    }

    public function isRecalculating()
    {
        return $this->recalculating;
    }
}
