<?php

namespace App\Observers;

use App\Events\VariantChanged;
use App\Variant;

class VariantObserver
{
    public function deleted(Variant $variant)
    {
        VariantChanged::dispatch($variant);
    }
}
