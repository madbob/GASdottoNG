<?php

namespace App\Parameters\ModifierTarget;

class Supplier extends Order
{
    public function configParams()
    {
        $ret = parent::configParams();
        $ret['always_on'] = true;
        return $ret;
    }
}
