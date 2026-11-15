<?php

namespace App\Parameters\ModifierTarget;

class Order extends ModifierTarget
{
    public function modelType(): string
    {
        return 'order';
    }
}
