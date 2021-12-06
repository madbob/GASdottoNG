<?php

namespace App\Parameters\PaymentType;

abstract class PaymentType
{
    public function enabled()
    {
        return true;
    }

    public abstract function identifier();
    public abstract function definition();
}
