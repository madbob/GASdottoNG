<?php

namespace App\Parameters\MailTypes;

use App\Parameters\Parameter;

abstract class MailType extends Parameter
{
    public abstract function description();
    public abstract function params();
    public abstract function enabled($gas);
}
