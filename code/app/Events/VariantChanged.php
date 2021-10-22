<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class VariantChanged
{
    use Dispatchable, SerializesModels;

    public $variant;

    public function __construct($variant)
    {
        $this->variant = $variant;
    }
}
