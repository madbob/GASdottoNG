<?php

namespace App\Printers\Concerns;

use App\Printers\Components\Table;
use App\Printers\Components\Header;
use App\Formatters\Order as OrderFormatter;
use App\Delivery;

trait Summary
{
    private function formatSummaryShipping($order, $fields, $internal_offsets, $shipping_place)
    {
        $rows = [];
        $total = 0;
        $formattable = OrderFormatter::formattableColumns('summary');
        $summary = $order->reduxData(['shipping_place' => $shipping_place]);

        $price_offset = null;
        if (in_array('price', $fields)) {
            $price_offset = array_search('price', $fields);
        }

        foreach ($order->products()->sorted()->get() as $product) {
            $row = $this->formatProduct($fields, $formattable, $summary->products[$product->id] ?? null, $product, $internal_offsets);
            if (empty($row) == false) {
                if (is_null($price_offset) == false) {
                    $total = array_reduce($row, fn($carry, $r) => $carry + guessDecimal($r[$price_offset]), $total);
                }

                $rows = array_merge($rows, $row);
            }
        }

        if (empty($rows) == false) {
            $headers = array_map(fn($f) => $formattable[$f]->name, $fields);

            if (is_null($price_offset) == false) {
                $last_row = array_fill(0, count($fields), '');
                $last_row[0] = _i('Totale');
                $last_row[$price_offset] = printablePrice($total, ',');
                $rows[] = $last_row;
            }

            return new Table($headers, $rows);
        }
        else {
            return null;
        }
    }

    protected function formatSummary($order, $document, $fields, $status, $shipping_place)
    {
        $internal_offsets = $this->offsetsByStatus($status);

        if ($shipping_place && $shipping_place == 'all_by_place') {
            $places = Delivery::orderBy('name', 'asc')->get();
            foreach($places as $place) {
                $table = $this->formatSummaryShipping($order, $fields, $internal_offsets, $place->id);
                if ($table) {
                    $document->append(new Header($place->printableName()));
                    $document->append($table);
                }
            }
        }
        else {
            $table = $this->formatSummaryShipping($order, $fields, $internal_offsets, $shipping_place);
            if ($table) {
                $document->append($table);
            }
        }

        return $document;
    }
}
