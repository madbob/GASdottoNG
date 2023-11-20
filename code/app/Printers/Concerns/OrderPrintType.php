<?php

namespace App\Printers\Concerns;

trait OrderPrintType
{
    protected function filterExtraModifiers($modifiers, $extras)
    {
        if ($extras == false) {
            $modifiers = $modifiers->filter(function($mod) {
                return is_null($mod->modifier->movementType);
            });
        }

        return $modifiers;
    }
}
