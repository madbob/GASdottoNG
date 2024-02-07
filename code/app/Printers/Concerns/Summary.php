<?php

namespace App\Printers\Concerns;

use App\Printers\Components\Table;
use App\Printers\Components\Header;
use App\Formatters\Order as OrderFormatter;
use App\Delivery;
use App\ModifiedValue;

trait Summary
{
    use OrderPrintType;

    private function getPriceOffsetFromFields($fields)
    {
        $price_offset = null;

        if (in_array('price', $fields)) {
            $price_offset = array_search('price', $fields);
        }

        return $price_offset;
    }

    private function addModifiers($order, $summary, $status, $total, $fields, $extra_modifiers)
    {
        $rows = [];

        $modifiers = $order->applyModifiers($summary, $status);
        $modifiers = $this->filterExtraModifiers($modifiers, $extra_modifiers);
        $price_offset = $this->getPriceOffsetFromFields($fields);

        foreach (ModifiedValue::aggregateByType($modifiers) as $am) {
            $mod_row = array_fill(0, count($fields), '');
            $mod_row[0] = $am->name;
            $mod_row[$price_offset] = printablePrice($am->amount);
            $rows[] = $mod_row;
            $total += $am->amount;
        }

        if (empty($rows) == false) {
            $last_row = array_fill(0, count($fields), '');
            $last_row[0] = _i('Totale con Modificatori');
            $last_row[$price_offset] = printablePrice($total);
            $rows[] = $last_row;
        }

        return $rows;
    }

    private function formatSummaryShipping($order, $fields, $status, $shipping_place, $extra_modifiers)
    {
        $rows = [];
        $total = 0;
        $formattable = OrderFormatter::formattableColumns('summary');
        $summary = $order->reduxData(['shipping_place' => $shipping_place]);
        $internal_offsets = $this->offsetsByStatus($status);
        $price_offset = $this->getPriceOffsetFromFields($fields);

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
                $last_row[$price_offset] = printablePrice($total);
                $rows[] = $last_row;

                $modifiers_rows = $this->addModifiers($order, $summary, $status, $total, $fields, $extra_modifiers);
                $rows = array_merge($rows, $modifiers_rows);
            }

            return new Table($headers, $rows);
        }
        else {
            return null;
        }
    }

    protected function formatSummary($order, $document, $fields, $status, $shipping_place, $extra_modifiers)
    {
        if ($shipping_place && $shipping_place == 'all_by_place') {
            $places = Delivery::orderBy('name', 'asc')->get();
            foreach($places as $place) {
                $table = $this->formatSummaryShipping($order, $fields, $status, $place->id, $extra_modifiers);
                if ($table) {
                    $document->append(new Header($place->printableName()));
                    $document->append($table);
                }
            }
        }
        else {
            $table = $this->formatSummaryShipping($order, $fields, $status, $shipping_place, $extra_modifiers);
            if ($table) {
                $document->append($table);
            }
        }

        return $document;
    }
}
