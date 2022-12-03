<?php

namespace App\Printers;

use Auth;
use PDF;
use Mail;

use App\Formatters\User as UserFormatter;
use App\Notifications\GenericOrderShipping;
use App\Booking;

class Order extends Printer
{
    private function orderTopBookingsByShipping($order, $shipping_place, $status = null)
    {
        $bookings = $order->topLevelBookings($status);
        return Booking::sortByShippingPlace($bookings, $shipping_place);
    }

    private function sendDocumentMail($request, $temp_file_path)
    {
        $recipient_mails = $request['recipient_mail_value'] ?? [];

        $real_recipient_mails = array_map(function($item) {
            return (object) ['email' => $item];
        }, array_filter($recipient_mails));

        if (empty($real_recipient_mails)) {
            return;
        }

        Mail::to($real_recipient_mails)->send(new GenericOrderShipping($temp_file_path, $request['subject_mail'], $request['body_mail']));
        @unlink($temp_file_path);
    }

    private function handleShipping($obj, $request)
    {
        $send_mail = isset($request['send_mail']);
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'] ?? 'booked';
        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

        $shipping_place = $request['shipping_place'] ?? 'all_by_name';
        $data = $obj->formatShipping($fields, $status, $shipping_place);

        $title = _i('Dettaglio Consegne ordine %s presso %s', [$obj->internal_number, $obj->supplier->name]);
        $filename = sanitizeFilename($title . '.' . $subtype);
        $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', ['fields' => $fields, 'order' => $obj, 'data' => $data]);
            enablePdfPagesNumbers($pdf);

            if ($send_mail) {
                $pdf->save($temp_file_path);
            }
            else {
                return $pdf->download($filename);
            }
        }
        else if ($subtype == 'csv') {
            $flat_contents = [];

            foreach($data->contents as $c) {
                foreach($c->products as $p) {
                    $flat_contents[] = array_merge($c->user, $p);
                }

                /*
                    TODO: aggiungere anche i modificatori.
                    Devono essere formattati in Order::formatShipping(),
                    coerentemente alla formattazione dei prodotti
                */
            }

            if ($send_mail) {
                output_csv($filename, $data->headers, $flat_contents, function($row) {
                    return $row;
                }, $temp_file_path);
            }
            else {
                return output_csv($filename, $data->headers, $flat_contents, function($row) {
                    return $row;
                });
            }
        }

        if ($send_mail) {
            $this->sendDocumentMail($request, $temp_file_path);
        }
    }

    private function handleSummary($obj, $request)
    {
        $send_mail = isset($request['send_mail']);
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'];
        $required_fields = $request['fields'] ?? [];

        $shipping_place = $request['shipping_place'] ?? 'all_by_place';
        if ($shipping_place == 'all_by_place') {
            $shipping_place = null;
        }

        if ($send_mail) {
            $temp_file_path = $obj->document('summary', $subtype, 'save', $required_fields, $status, $shipping_place);
            $this->sendDocumentMail($request, $temp_file_path);
        }
        else {
            return $obj->document('summary', $subtype, 'return', $required_fields, $status, $shipping_place);
        }
    }

    private function formatTableRows($order, $shipping_place, $status, $fields, &$all_products)
    {
        $bookings = $this->orderTopBookingsByShipping($order, $shipping_place, $status == 'saved' ? 'saved' : null);

        if ($status == 'saved' || $status == 'delivered') {
            $get_total = 'delivered';
            $get_function = 'getDeliveredQuantity';
            $get_function_real = false;
        }
        else {
            $get_total = 'booked';
            $get_function = 'getBookedQuantity';
            $get_function_real = true;
        }

        $data = [];
        $total_price = 0;

        foreach($bookings as $booking) {
            $row = UserFormatter::format($booking->user, $fields->user_columns);

            foreach ($order->products as $product) {
                if ($product->variants->isEmpty()) {
                    $quantity = $booking->$get_function($product, $get_function_real, true);
                    $all_products[$product->id] += $quantity;
                    $row[] = printableQuantity($quantity, $product->measure->discrete, 3);
                }
                else {
                    foreach($product->variant_combos as $combo) {
                        $quantity = $booking->$get_function($combo, $get_function_real, true);
                        $all_products[$product->id . '-' . $combo->id] += $quantity;
                        $row[] = printableQuantity($quantity, $product->measure->discrete, 3);
                    }
                }
            }

            $price = $booking->getValue($get_total, true);
            $total_price += $price;
            $row[] = printablePrice($price);

            $data[] = $row;
        }

        return [$data, $total_price];
    }

    private function handleTable($obj, $request)
    {
        $status = $request['status'] ?? 'booked';
        $shipping_place = $request['shipping_place'] ?? 0;

        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

        $user = Auth::user();
        $all_products = [];

        /*
            Formatto riga di intestazione
        */

        $user_columns = UserFormatter::getHeaders($fields->user_columns);
        $headers = $user_columns;

        foreach ($obj->products as $product) {
            if ($product->variants->isEmpty()) {
                $all_products[$product->id] = 0;
                $headers[] = sprintf('%s (%s)', $product->printableName(), printablePriceCurrency($product->price));
            }
            else {
                foreach($product->variant_combos as $combo) {
                    $all_products[$product->id . '-' . $combo->id] = 0;
                    $headers[] = sprintf('%s (%s)', $combo->printableName(), printablePriceCurrency($combo->price));
                }
            }
        }

        $headers[] = _i('Totale Prezzo');

        /*
            Formatto righe delle singole prenotazioni
        */

        list($data, $total_price) = $this->formatTableRows($obj, $shipping_place, $status, $fields, $all_products);

        /*
            Formatto riga dei totali
        */

        $row = [];

        $row[] = _i('Totale');

        for($i = 1; $i < count($user_columns); $i++) {
            $row[] = '';
        }

        foreach ($obj->products as $product) {
            if ($product->variants->isEmpty()) {
                $row[] = printableQuantity($all_products[$product->id], $product->measure->discrete, 3);
            }
            else {
                foreach($product->variant_combos as $combo) {
                    $row[] = printableQuantity($all_products[$product->id . '-' . $combo->id], $product->measure->discrete, 3);
                }
            }
        }

        $row[] = printablePrice($total_price);

        $data[] = $row;
        $data[] = $headers;

        /*
            Genero documento
        */

        $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$obj->internal_number, $obj->supplier->name]));
        return output_csv($filename, $headers, $data, null);
    }

    public function document($obj, $type, $request)
    {
        switch ($type) {
            case 'shipping':
                return $this->handleShipping($obj, $request);

            case 'summary':
                return $this->handleSummary($obj, $request);

            case 'table':
                return $this->handleTable($obj, $request);

            default:
                \Log::error('Unrecognized type for Order document: ' . $type);
                return null;
        }
    }
}
