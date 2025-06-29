<?php

/*
    Attenzione: questo formatter è un po' anomalo, va usato diversamente
    rispetto a tutti gli altri.
    TODO: uniformare l'API
*/

namespace App\Formatters;

class Order extends Formatter
{
    private static function formatCode()
    {
        return (object) [
            'name' => __('texts.products.code'),
            'format_product' => function ($product, $summary) {
                return $product->supplier_code;
            },
            'format_variant' => function ($product, $summary) {
                if (! empty($summary->variant->supplier_code)) {
                    return $summary->variant->supplier_code;
                }
                else {
                    return $summary->variant->product->product->supplier_code;
                }
            },
        ];
    }

    private static function formatQuantity()
    {
        return (object) [
            'name' => __('texts.generic.quantity'),
            'checked' => true,
            'format_product' => function ($product, $summary, $alternate = false) {
                if ($alternate === false) {
                    return printableQuantity($summary->quantity_pieces, $product->measure->discrete, 2);
                }
                else {
                    return printableQuantity($summary->delivered_pieces, $product->measure->discrete, 2);
                }
            },
        ];
    }

    private static function formatBoxes()
    {
        return (object) [
            'name' => __('texts.orders.boxes'),
            'format_product' => function ($product, $summary, $alternate = false) {
                if ($product->package_size != 0) {
                    if ($alternate === false) {
                        return $summary->quantity_pieces / $product->package_size;
                    }
                    else {
                        return $summary->delivered_pieces / $product->package_size;
                    }
                }
                else {
                    return '';
                }
            },
        ];
    }

    private static function formatMeasure()
    {
        return (object) [
            'name' => __('texts.generic.measure'),
            'checked' => true,
            'format_product' => function ($product, $summary, $alternate = false) {
                if ($alternate === false) {
                    return $product->printableMeasure(true);
                }
                else {
                    if ($product->portion_quantity != 0) {
                        return $product->measure->name;
                    }
                    else {
                        return $product->printableMeasure(true);
                    }
                }
            },
        ];
    }

    private static function formatPrice()
    {
        return (object) [
            'name' => __('texts.generic.price'),
            'checked' => true,
            'format_product' => function ($product, $summary, $alternate = false) {
                if ($alternate === false) {
                    return printablePrice($summary->price);
                }
                else {
                    return printablePrice($summary->price_delivered);
                }
            },
        ];
    }

    public static function formattableColumns($type = null)
    {
        $ret = [
            'name' => (object) [
                'name' => __('texts.user.firstname'),
                'checked' => true,
                'format_product' => function ($product, $summary) {
                    return $product->printableName();
                },
                'format_variant' => function ($product, $summary) {
                    return $product->printableName() . ' - ' . $summary->variant->printableName();
                },
            ],
            'supplier' => (object) [
                'name' => __('texts.orders.supplier'),
                'checked' => false,
                'format_product' => function ($product, $summary) {
                    return $product->supplier->printableName();
                },
            ],

            'code' => self::formatCode(),
            'quantity' => self::formatQuantity(),
            'boxes' => self::formatBoxes(),
            'measure' => self::formatMeasure(),

            'category' => (object) [
                'name' => __('texts.generic.category'),
                'checked' => false,
                'format_product' => function ($product, $summary) {
                    return $product->category ? $product->category->name : '';
                },
            ],
            'unit_price' => (object) [
                'name' => __('texts.products.prices.unit'),
                'checked' => false,
                'format_product' => function ($product, $summary) {
                    return printablePrice($product->getPrice());
                },
                'format_variant' => function ($product, $summary) {
                    return printablePrice($summary->variant->unitPrice());
                },
            ],

            'price' => self::formatPrice(),

            'time' => (object) [
                'name' => __('texts.orders.booking_date_time'),
                'checked' => false,
                'format_product' => function ($product, $summary) {
                    return $summary->booked->created_at->format('d/m/Y H:i');
                },
                'format_variant' => function ($product, $summary) {
                    return $summary->variant->created_at->format('d/m/Y H:i');
                },
            ],
        ];

        if ($type == 'summary') {
            $ret['notes'] = (object) [
                'name' => __('texts.generic.notes'),
                'format_product' => function ($product, $summary) {
                    return $product->pivot->notes;
                },
            ];
        }

        return $ret;
    }
}
