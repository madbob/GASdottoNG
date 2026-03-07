<?php

namespace App\Printers\Concerns;

use App\Printers\Components\Table;
use App\Printers\Components\Header;
use App\Formatters\Order as OrderFormatter;
use App\ModifiedValue;

trait Summary
{
    private function addModifiers($order, $summary, $params, $total)
    {
        $rows = [];

        $modifiers = $order->applyModifiers($summary, $params->status);
        $modifiers = $this->filterExtraModifiers($modifiers, $params->extra_modifiers);
        $price_offset = $params->getPriceOffset(true);

        foreach (ModifiedValue::aggregateByType($modifiers) as $am) {
            $mod_row = array_fill(0, count($params->required_fields), '');
            $mod_row[0] = $am->name;

            if ($price_offset != null) {
                $mod_row[$price_offset] = printablePrice($am->total_amount);
            }

            $rows[] = $mod_row;
            $total += $am->amount;
        }

        if (empty($rows) === false) {
            $last_row = array_fill(0, count($params->required_fields), '');
            $last_row[0] = __('texts.orders.totals.with_modifiers');

            if ($price_offset != null) {
                $last_row[$price_offset] = printablePrice($total);
            }

            $rows[] = $last_row;
        }

        return $rows;
    }

    private function formatSummaryShipping($order, $params, $circles)
    {
        $rows = [];
        $total = 0;
        $formattable = OrderFormatter::formattableColumns('summary');
        $summary = $order->reduxData(['circles' => $circles]);
        $internal_offsets = $this->offsetsByStatus($params->status);
        $price_offset = $params->getPriceOffset(false);

        foreach ($order->products()->sorted()->get() as $product) {
            $row = $this->formatProduct($params->fields->product_columns, $formattable, $summary->products[$product->id] ?? null, $product, $internal_offsets);
            if (empty($row) === false) {
                if ($price_offset != null) {
                    $total = array_reduce($row, fn ($carry, $r) => $carry + guessDecimal($r[$price_offset]), $total);
                }

                $rows = array_merge($rows, $row);
            }
        }

        if (empty($rows) === false) {
            $headers = array_map(fn ($f) => $formattable[$f]->name, $params->fields->product_columns);

            if ($price_offset != null) {
                $last_row = array_fill(0, count($params->required_fields), '');
                $last_row[0] = __('texts.orders.totals.total');
                $last_row[$price_offset] = printablePrice($total);
                $rows[] = $last_row;

                $modifiers_rows = $this->addModifiers($order, $summary, $params, $total);
                $rows = array_merge($rows, $modifiers_rows);
            }

            return new Table($headers, $rows);
        }
        else {
            return null;
        }
    }

    protected function formatSummary($order, $document, $params, $circles)
    {
        if ($circles->getMode() == 'all_by_place') {
            foreach ($circles->combinations() as $combo) {
                $table = $this->formatSummaryShipping($order, $params, $combo);
                if ($table) {
                    $document->append(new Header($combo->printableName()));
                    $document->append($table);
                }
            }
        }
        else {
            $table = $this->formatSummaryShipping($order, $params, $circles);
            if ($table) {
                $document->append($table);
            }
        }

        return $document;
    }
}
