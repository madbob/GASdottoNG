<?php

/**
 * In questa classe si formattano i testi destinati a descrivere il
 * comportamento di un modificatore in fase di modifica.
 * Esiste una enorme quantità di combinazioni, dunque qui cerco di limitare le
 * permutazioni effettivamente gestite dal Model preso in esame onde evitare di
 * generare mega e mega di testo solo in minima parte poi usati.
 */

namespace App\View\Texts;

use App\Product;

class Modifier
{
    private static function valueLabels($target)
    {
        $config = $target->getModifierDefinition()->configParams();

        $ret = [
            'none' => ''
        ];

        foreach($config['applies_types'] as $type => $name) {
            if ($type !== 'none') {
                $ret[$type] = __('texts.modifiers.dynamics.values.' . $type);
            }
        }

        return $ret;
    }

    private static function targetsLabels($target)
    {
        $definition = $target->getModifierDefinition();
        $config = $definition->configParams();
        $modeltype = $definition->modelType();

        $ret = [];

        foreach($config['applies_targets'] as $type => $name) {
            $ret[$modeltype . ',' . $type] = __('texts.modifiers.dynamics.targets.' . $modeltype . '.' . $type);
        }

        return $ret;
    }

    private static function scaleLabels()
    {
        return [
            'minor' => __('texts.modifiers.dynamics.scale.minor'),
            'major' => __('texts.modifiers.dynamics.scale.major'),
        ];
    }

    private static function unitLabels($target)
    {
        $currency = defaultCurrency()->symbol;

        if (is_a($target, Product::class) && $target->measure) {
            $quantity_label = $target->measure->name;
        }
        else {
            $quantity_label = __('texts.products.list');
        }

        return [
            /*
                La 'X' serve a inizializzare l'input group nell'editor del
                modificatore, di fatto non viene mai visualizzata
            */
            'none' => 'X',
            'quantity' => $quantity_label,
            'price' => $currency,
            'order_price' => $currency,
            'weight' => __('texts.generic.kilos'),
        ];
    }

    private static function distributionLabels($target)
    {
        $currency = defaultCurrency()->symbol;
        $definition = $target->getModifierDefinition()->configParams();

        $ret = [];

        foreach($definition['applies_targets'] as $identifier => $name) {
            $ret['sum,' . $identifier . ',absolute'] = [__('texts.modifiers.dynamics.distribution.sum.' . $identifier), $currency];
            $ret['sum,' . $identifier . ',percentage'] = [__('texts.modifiers.dynamics.distribution.sum.' . $identifier), '%'];
            $ret['sum,' . $identifier . ',mass'] = [__('texts.modifiers.dynamics.distribution.sum.' . $identifier . '_kg'), $currency];

            $ret['sub,' . $identifier . ',absolute'] = [__('texts.modifiers.dynamics.distribution.sub.' . $identifier), $currency];
            $ret['sub,' . $identifier . ',percentage'] = [__('texts.modifiers.dynamics.distribution.sub.' . $identifier), '%'];
            $ret['sub,' . $identifier . ',mass'] = [__('texts.modifiers.dynamics.distribution.sub.' . $identifier . '_kg'), $currency];

            $ret['passive,' . $identifier . ',absolute'] = [__('texts.modifiers.dynamics.distribution.passive.' . $identifier), $currency];
            $ret['passive,' . $identifier . ',percentage'] = [__('texts.modifiers.dynamics.distribution.passive.' . $identifier), '%'];
            $ret['passive,' . $identifier . ',mass'] = [__('texts.modifiers.dynamics.distribution.passive.' . $identifier . '_kg'), $currency];
        }

        if (is_a($target, Product::class)) {
            $ret['apply,product,price'] = [__('texts.modifiers.dynamics.distribution.apply.product'), $currency];
            $ret['apply,booking,price'] = [__('texts.modifiers.dynamics.distribution.apply.product'), $currency];
            $ret['apply,order,price'] = [__('texts.modifiers.dynamics.distribution.apply.product'), $currency];
        }

        return $ret;
    }

    private static function typesLabels()
    {
        return [
            'none' => '',
            'quantity' => __('texts.modifiers.dynamics.types.quantity'),
            'price' => __('texts.modifiers.dynamics.types.price'),
            'weight' => __('texts.modifiers.dynamics.types.weight'),
        ];
    }

    public static function descriptions($target)
    {
        /*
            Qui predispongo le stringhe descrittive per tutte le possibili
            combinazioni di valori, destinate a rendere più comprensibile la
            tabella delle soglie.
        */

        $value_labels = self::valueLabels($target);
        $targets_labels = self::targetsLabels($target);
        $scale_labels = self::scaleLabels();
        $value_units = self::unitLabels($target);
        $distribution_labels = self::distributionLabels($target);
        $distribution_types = self::typesLabels();

        $labels = [];

        foreach ($value_labels as $vl => $vs) {
            foreach ($targets_labels as $tl => $ts) {
                foreach ($scale_labels as $sl => $ss) {
                    foreach ($value_units as $vu => $vus) {
                        foreach ($distribution_labels as $dl => $ds) {
                            foreach ($distribution_types as $dt => $dts) {
                                $key = sprintf('%s,%s,%s,%s,%s,%s', $vl, $tl, $sl, $vu, $dl, $dt);
                                $labels[$key] = [
                                    __('texts.modifiers.dynamics.template', [
                                        'value' => $vs,
                                        'target' => $ts,
                                        'scale' => $ss
                                    ]),
                                    $vus,
                                    $ds[0],
                                    $ds[1],
                                    $dts,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $labels;
    }
}
