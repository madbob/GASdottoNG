<?php

namespace App\Printers;

abstract class Printer
{
    abstract public function document($obj, $type, $request);
}
