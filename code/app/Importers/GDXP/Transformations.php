<?php

namespace App\Importers\GDXP;

use Illuminate\Support\Collection;

use App\Modifier;
use App\ModifierType;
use App\Product;

class Transformations extends GDXPImporter
{
    public static function readXML($xml)
    {
        return null;
    }

    public static function importXML($xml, $replace)
    {
        return null;
    }

    public static function readJSON($json)
    {
        return null;
    }

    private static function readDefinition($modifier, $json)
    {
        $definitions = [];

        if (isset($json->fixed)) {
            $modifier->applies_type = 'none';
            $amount = $json->fixed;

            if (isPercentage($amount)) {
                $modifier->value = 'percentage';
            }

            $amount = (float) trim(str_replace('%', '', $amount));

            $definitions[] = [
                'threshold' => PHP_INT_MIN,
                'amount' => $amount,
            ];
        }
        else {
            $modifier->applies_type = $json->variable->theshold_type;

            $variables = $json->variable->thesholds ?? [];
            foreach($variables as $index => $var) {
                $amount = $var->amount;

                if ($index == 0) {
                    if (isPercentage($amount)) {
                        $modifier->value = 'percentage';
                    }
                }

                $amount = (float) trim(str_replace('%', '', $amount));

                $definitions[] = [
                    'threshold' => $var->theshold,
                    'amount' => $amount,
                ];
            }
        }

        $modifier->definition = json_encode($definitions);
    }

    public static function importJSON($target, $json)
    {
        $type = $json->type;

        /*
            Qui si assume che le tipologie delle trasformazioni GDXP
            corrispondano alle tipologie di modificatori di sistema
        */
        $modifier = $target->modifiers()->whereHas('modifierType', function($query) use ($type) {
            $query->where('identifier', $type);
        })->first();

        if (is_null($modifier)) {
            $modifier = new Modifier();
            $modifier->modifier_type_id = ModifierType::where('identifier', $type)->first()->id;
            $modifier->target_id = $target->id;
            $modifier->target_type = get_class($target);
        }

        switch($json->operation) {
            case 'sum':
                $modifier->arithmetic = 'sum';
                break;
            default:
                $modifier->arithmetic = 'sub';
                break;
        }

        $modifier->scale = 'major';
        $modifier->value = 'absolute';
        $modifier->applies_target = is_a($target, Product::class) ? 'product' : 'order';

        if (empty($modifier->distribution_type) || $modifier->distribution_type == 'none') {
            $modifier->distribution_type = 'price';
        }

        self::readDefinition($modifier, $json);

        $modifier->save();
    }
}
