<?php

/*
    Classe per formattare la "Tabella Complessiva Prodotti" di ordini e
    aggregati
*/

namespace App\Printers\Concerns;

trait Table
{
    protected function formatTableHead($user_columns, $orders)
    {
        $all_products = [];
        $headers = $user_columns;
        $prices_rows = array_fill(0, count($user_columns), '');

        foreach ($orders as $order) {
            foreach ($order->product_concepts as $product) {
                $key = $product->getConceptID();
                $all_products[$key] = 0;
                $headers[] = $product->printableName();
                $prices_rows[] = printablePrice($product->getPrice());
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

        foreach ($order->product_concepts as $product) {
            $quantity = 0;
            if ($booking) {
                $quantity = $booking->$get_function($product, false, true);
            }

            $key = $product->getConceptID();
            $all_products[$key] += $quantity;
            $row[] = printableQuantity($quantity, $product->measure->discrete, 3);
        }

        return $row;
    }

    protected function formatTableFooter($orders, $user_columns, $all_products, $total_price)
    {
        $row = [];

        $row[] = _i('Totale');
        $row = array_merge($row, array_fill(0, count($user_columns) - 1, ''));

        foreach ($orders as $order) {
            foreach ($order->product_concepts as $product) {
                $key = $product->getConceptID();
                $row[] = printableQuantity($all_products[$key], $product->measure->discrete, 3);
            }
        }

        $row[] = printablePrice($total_price);
        return $row;
    }

    /*
        Per eliminare, ove richiesto, le colonne dei prodotti non prenotati (con
        prezzo totale = 0)
    */
    protected function compressTable($user_columns, $data)
    {
        $compressed = [];

        $user_columns_count = count($user_columns);
        foreach($data as $index => $row) {
            $compressed[] = array_slice($row, 0, $user_columns_count);
        }

        /*
            Qui si assume che la riga coi totali sia la penultima, perché
            l'ultima contiene una ripetizione della riga di intestazione coi
            nomi dei prodotti
        */
        $reference_row = $data[count($data) - 2];
        $len = count($reference_row);

        for ($i = count($user_columns); $i < $len; $i++) {
            if (translateNumberFormat($reference_row[$i]) != 0) {
                foreach($data as $index => $row) {
                    $compressed[$index][] = $row[$i];
                }
            }
        }

        return $compressed;
    }
}
