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
        $formattable = OrderFormatter::formattableColumns('summary');
        $summary = $order->reduxData(['shipping_place' => $shipping_place]);

        foreach ($order->products()->sorted()->get() as $product) {
            $row = $this->formatProduct($fields, $formattable, $summary->products[$product->id] ?? null, $product, $internal_offsets);
            if (!empty($row)) {
                $rows = array_merge($rows, $row);
            }
        }

        if (empty($rows) == false) {
            $headers = [];

            foreach($fields as $f) {
                $headers[] = $formattable[$f]->name;
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
