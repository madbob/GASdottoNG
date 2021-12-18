<?php

namespace App\Parameters\Constraints;

abstract class Constraint
{
    public abstract function printable($product, $order);
    public abstract function test($booked, $quantity);
}
