<?php

namespace App\Printers;

abstract class Printer
{
    public abstract function document($obj, $type, $request);
}
