<?php

namespace App\View\Texts;

use App\Product;

class Modifier
{
    private static function valueLabels()
    {
        return [
            'none' => '',
            'quantity' => __('modifiers.dynamics.values.quantity'),
            'price' => __('modifiers.dynamics.values.price'),
            'order_price' => __('modifiers.dynamics.values.order_price'),
            'weight' => __('modifiers.dynamics.values.weight'),
        ];
    }

    private static function targetsLabels()
    {
        return [
            'product,product' => '',
            'product,booking' => __('modifiers.dynamics.targets.product.booking'),
            'product,order' => __('modifiers.dynamics.targets.product.order'),
            'order,product' => '',
            'order,booking' => __('modifiers.dynamics.targets.order.booking'),
            'order,order' => __('modifiers.dynamics.targets.order.order'),
            'aggregate,product' => '',
            'aggregate,booking' => __('modifiers.dynamics.targets.aggregate.booking'),
            'aggregate,order' => __('modifiers.dynamics.targets.aggregate.order'),
            'circle,product' => '',
            'circle,booking' => __('modifiers.dynamics.targets.circle.booking'),
            'circle,order' => __('modifiers.dynamics.targets.circle.order'),
        ];
    }

    private static function scaleLabels()
    {
        return [
            'minor' => __('modifiers.dynamics.scale.minor'),
            'major' => __('modifiers.dynamics.scale.major'),
        ];
    }

    private static function unitLabels($target)
    {
        $currency = defaultCurrency()->symbol;

        if (is_a($target, Product::class)) {
            $quantity_label = $target->measure->name;
        }
        else {
            $quantity_label = __('products.list');
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
            'weight' => __('generic.kilos'),
        ];
    }

    private static function distributionLabels()
    {
        $currency = defaultCurrency()->symbol;

        return [
            'sum,product,absolute' => [__('modifiers.dynamics.distribution.sum.product'), $currency],
            'sum,booking,absolute' => [__('modifiers.dynamics.distribution.sum.booking'), $currency],
            'sum,order,absolute' => [__('modifiers.dynamics.distribution.sum.order'), $currency],
            'sum,product,percentage' => [__('modifiers.dynamics.distribution.sum.product'), '%'],
            'sum,booking,percentage' => [__('modifiers.dynamics.distribution.sum.booking'), '%'],
            'sum,order,percentage' => [__('modifiers.dynamics.distribution.sum.order'), '%'],
            'sum,product,mass' => [__('modifiers.dynamics.distribution.sum.product_kg'), $currency],
            'sum,booking,mass' => [__('modifiers.dynamics.distribution.sum.booking_kg'), $currency],
            'sum,order,mass' => [__('modifiers.dynamics.distribution.sum.order_kg'), $currency],

            'sub,product,absolute' => [__('modifiers.dynamics.distribution.sub.product'), $currency],
            'sub,booking,absolute' => [__('modifiers.dynamics.distribution.sub.booking'), $currency],
            'sub,order,absolute' => [__('modifiers.dynamics.distribution.sub.order'), $currency],
            'sub,product,percentage' => [__('modifiers.dynamics.distribution.sub.product'), '%'],
            'sub,booking,percentage' => [__('modifiers.dynamics.distribution.sub.booking'), '%'],
            'sub,order,percentage' => [__('modifiers.dynamics.distribution.sub.order'), '%'],
            'sub,product,mass' => [__('modifiers.dynamics.distribution.sub.product_kg'), $currency],
            'sub,booking,mass' => [__('modifiers.dynamics.distribution.sub.booking_kg'), $currency],
            'sub,order,mass' => [__('modifiers.dynamics.distribution.sub.order_kg'), $currency],

            'passive,product,absolute' => [__('modifiers.dynamics.distribution.passive.product'), $currency],
            'passive,booking,absolute' => [__('modifiers.dynamics.distribution.passive.booking'), $currency],
            'passive,order,absolute' => [__('modifiers.dynamics.distribution.passive.order'), $currency],
            'passive,product,percentage' => [__('modifiers.dynamics.distribution.passive.product'), '%'],
            'passive,booking,percentage' => [__('modifiers.dynamics.distribution.passive.booking'), '%'],
            'passive,order,percentage' => [__('modifiers.dynamics.distribution.passive.order'), '%'],
            'passive,product,mass' => [__('modifiers.dynamics.distribution.passive.product_kg'), $currency],
            'passive,booking,mass' => [__('modifiers.dynamics.distribution.passive.booking_kg'), $currency],
            'passive,order,mass' => [__('modifiers.dynamics.distribution.passive.order_kg'), $currency],

            'apply,product,price' => [__('modifiers.dynamics.distribution.apply.product'), $currency],
            'apply,booking,price' => [__('modifiers.dynamics.distribution.apply.product'), $currency],
            'apply,order,price' => [__('modifiers.dynamics.distribution.apply.product'), $currency],
        ];
    }

    private static function typesLabels()
    {
        return [
            'none' => '',
            'quantity' => __('modifiers.dynamics.types.quantity'),
            'price' => __('modifiers.dynamics.types.price'),
            'weight' => __('modifiers.dynamics.types.weight'),
        ];
    }

    public static function descriptions($target)
    {
        /*
            Qui predispongo le stringhe descrittive per tutte le possibili
            combinazioni di valori, destinate a rendere più comprensibile la
            tabella delle soglie.
        */

        $value_labels = self::valueLabels();
        $targets_labels = self::targetsLabels();
        $scale_labels = self::scaleLabels();
        $value_units = self::unitLabels($target);
        $distribution_labels = self::distributionLabels();
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
                                    __('modifiers.dynamics.template', ['value' => $vs, 'target' => $ts, 'scale' => $ss]),
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
