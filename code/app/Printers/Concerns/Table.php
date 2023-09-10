<?php

namespace App\Printers\Concerns;

trait Table
{
    protected function formatTableHead($user_columns, $orders)
    {
        $all_products = [];
        $headers = $user_columns;
        $prices_rows = array_fill(0, count($user_columns), '');

        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                if ($product->variants->isEmpty()) {
                    $all_products[$product->id] = 0;
                    $headers[] = $product->printableName();
                    $prices_rows[] = printablePrice($product->getPrice(), ',');
                }
                else {
                    foreach($product->variant_combos as $combo) {
                        $all_products[$product->id . '-' . $combo->id] = 0;
                        $headers[] = $combo->printableName();
                        $prices_rows[] = printablePrice($combo->getPrice(), ',');
                    }
                }
            }
        }

        $headers[] = _i('Totale Prezzo');
        $prices_rows[] = '';

        return [$all_products, $headers, $prices_rows];
    }

    protected function formatBookingInTable($order, $booking, $status, &$all_products)
    {
        $row = [];
        list($get_total, $get_function) = $this->bookingsRules($status);

        foreach ($order->products as $product) {
            if ($product->variants->isEmpty()) {
                if ($booking) {
                    $quantity = $booking->$get_function($product, false, true);
                }
                else {
                    $quantity = 0;
                }

                $all_products[$product->id] += $quantity;
                $row[] = printableQuantity($quantity, $product->measure->discrete, 3, ',');
            }
            else {
                foreach($product->variant_combos as $combo) {
                    if ($booking) {
                        $quantity = $booking->$get_function($combo, false, true);
                    }
                    else {
                        $quantity = 0;
                    }

                    $all_products[$product->id . '-' . $combo->id] += $quantity;
                    $row[] = printableQuantity($quantity, $product->measure->discrete, 3, ',');
                }
            }
        }

        return $row;
    }

    protected function formatTableFooter($orders, $user_columns, $all_products, $total_price)
    {
        $row = [];

        $row[] = _i('Totale');
        $row = array_merge($row, array_fill(0, count($user_columns) - 1, ''));

        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                if ($product->variants->isEmpty()) {
                    $row[] = printableQuantity($all_products[$product->id], $product->measure->discrete, 3, ',');
                }
                else {
                    foreach($product->variant_combos as $combo) {
                        $row[] = printableQuantity($all_products[$product->id . '-' . $combo->id], $product->measure->discrete, 3, ',');
                    }
                }
            }
        }

        $row[] = printablePrice($total_price);
        return $row;
    }
}
