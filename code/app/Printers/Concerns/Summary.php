<?php

namespace App\Printers\Concerns;

use App\Printers\Components\Table;
use App\Printers\Components\Header;
use App\Formatters\Order as OrderFormatter;
use App\ModifiedValue;

trait Summary
{
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
            $mod_row[$price_offset] = printablePrice($am->total_amount);
            $rows[] = $mod_row;
            $total += $am->amount;
        }

        if (empty($rows) === false) {
            $last_row = array_fill(0, count($fields), '');
            $last_row[0] = __('orders.totals.with_modifiers');
            $last_row[$price_offset] = printablePrice($total);
            $rows[] = $last_row;
        }

        return $rows;
    }

    private function formatSummaryShipping($order, $fields, $status, $circles, $extra_modifiers)
    {
        $rows = [];
        $total = 0;
        $formattable = OrderFormatter::formattableColumns('summary');
        $summary = $order->reduxData(['circles' => $circles]);
        $internal_offsets = $this->offsetsByStatus($status);
        $price_offset = $this->getPriceOffsetFromFields($fields);

        foreach ($order->products()->sorted()->get() as $product) {
            $row = $this->formatProduct($fields, $formattable, $summary->products[$product->id] ?? null, $product, $internal_offsets);
            if (empty($row) === false) {
                if ($price_offset != null) {
                    $total = array_reduce($row, fn ($carry, $r) => $carry + guessDecimal($r[$price_offset]), $total);
                }

                $rows = array_merge($rows, $row);
            }
        }

        if (empty($rows) === false) {
            $headers = array_map(fn ($f) => $formattable[$f]->name, $fields);

            if ($price_offset != null) {
                $last_row = array_fill(0, count($fields), '');
                $last_row[0] = __('orders.totals.total');
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

    protected function formatSummary($order, $document, $fields, $status, $circles, $extra_modifiers)
    {
        if ($circles->getMode() == 'all_by_place') {
            foreach ($circles->combinations() as $combo) {
                $table = $this->formatSummaryShipping($order, $fields, $status, $combo, $extra_modifiers);
                if ($table) {
                    $document->append(new Header($combo->printableName()));
                    $document->append($table);
                }
            }
        }
        else {
            $table = $this->formatSummaryShipping($order, $fields, $status, $circles, $extra_modifiers);
            if ($table) {
                $document->append($table);
            }
        }

        return $document;
    }
}
