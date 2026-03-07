<?php

/*
    Classe per formattare la "Tabella Complessiva Prodotti" di ordini e
    aggregati
*/

namespace App\Printers\Concerns;

use App\Formatters\User as UserFormatter;
use App\Printers\PrintParams;
use App\Booking;

trait Table
{
    protected function realHandleTable($master, $aggregate, $orders, $request)
    {
        $params = new PrintParams($request, $master);

        /*
            Formatto riga di intestazione
        */

        $user_columns = UserFormatter::getHeaders($params->fields->user_columns);
        [$all_products, $headers, $prices_rows] = $this->formatTableHead($user_columns, $orders);

        /*
            Formatto righe delle singole prenotazioni
        */

        [$orders, $bookings] = $this->getFormattableDataForTable($master, $params);
        [$data, $total_price] = $this->formatTableRows($bookings, $orders, $params, $all_products);
        array_unshift($data, $prices_rows);

        /*
            Formatto riga dei totali
        */

        $row = $this->formatTableFooter($orders, $user_columns, $all_products, $total_price);
        $data[] = $row;
        $data[] = $headers;

        if ($params->include_missing == 'no') {
            $data = $this->compressTable($user_columns, $data);
            $headers = $data[count($data) - 1];
        }

        /*
            Genero documento
        */

        $filename = __('texts.orders.files.order.table') . '.csv';
        return $this->outputCsv($params, $filename, $headers, $data);
    }

    private function formatTableHead($user_columns, $orders)
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

        $headers[] = __('texts.orders.totals.total');
        $prices_rows[] = '';

        return [$all_products, $headers, $prices_rows];
    }

    private function formatTableRows($bookings, $orders, $params, &$all_products)
    {
        [$get_total, $get_function] = $this->bookingsRules($params->status);

        $data = [];
        $total_price = 0;

        foreach ($bookings as $booking) {
            $row = UserFormatter::format($booking->user, $params->fields->user_columns);

            foreach ($orders as $order) {
                if (is_a($booking, Booking::class)) {
                    $sub_booking = $booking;
                }
                else {
                    $sub_booking = $booking->getOrderBooking($order);
                }

                $subrow = $this->formatBookingInTable($order, $sub_booking, $params->status, $all_products);
                $row = array_merge($row, $subrow);
            }

            $price = $booking->getValue($get_total, true);
            $total_price += $price;
            $row[] = printablePrice($price);

            $data[] = $row;
        }

        return [$data, $total_price];
    }

    private function formatBookingInTable($order, $booking, $status, &$all_products)
    {
        $row = [];
        [$get_total, $get_function] = $this->bookingsRules($status);

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

    private function formatTableFooter($orders, $user_columns, $all_products, $total_price)
    {
        $row = [];

        $row[] = __('texts.orders.totals.total');
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
    private function compressTable($user_columns, $data)
    {
        $compressed = [];

        $user_columns_count = count($user_columns);
        foreach ($data as $index => $row) {
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
                foreach ($data as $index => $row) {
                    $compressed[$index][] = $row[$i];
                }
            }
        }

        return $compressed;
    }

    protected abstract function getFormattableDataForTable($master, $params);
}
