<?php

namespace App\Parameters\PaymentType;

use App\Parameters\Parameter;

abstract class PaymentType extends Parameter
{
    public function enabled()
    {
        return true;
    }

    abstract public function definition();
}
