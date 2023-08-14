<?php

namespace App\Printers;

use Auth;
use PDF;
use Mail;

use App\Formatters\User as UserFormatter;
use App\Notifications\GenericOrderShipping;
use App\Booking;
use App\Delivery;

class Order extends Printer
{
	use PrintingOrders;

    private function orderTopBookingsByShipping($order, $shipping_place, $status = null)
    {
        $bookings = $order->topLevelBookings($status);
        return Delivery::sortBookingsByShippingPlace($bookings, $shipping_place);
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

    protected function handleShipping($obj, $request)
    {
        $send_mail = isset($request['send_mail']);
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'] ?? 'booked';
        $extra_modifiers = $request['extra_modifiers'] ?? 0;
        $required_fields = $request['fields'] ?? [];

		/*
			Se viene richiesto un CSV ordinato per luogo di consegna, forzo
			l'inclusione di questo attributo tra i dati estratti per ottenere la
			griglia desiderata
		*/
        $shipping_place = $request['shipping_place'] ?? 'all_by_name';
		if ($shipping_place == 'all_by_place' && $subtype == 'csv') {
			if (in_array('shipping_place', $required_fields) == false) {
				$required_fields[] = 'shipping_place';
			}
		}

        $fields = splitFields($required_fields);

        $data = $obj->formatShipping($fields, $status, $shipping_place, $extra_modifiers);

        $title = _i('Dettaglio Consegne ordine %s presso %s', [$obj->internal_number, $obj->supplier->name]);
        $filename = sanitizeFilename($title . '.' . $subtype);
        $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', [
				'fields' => $fields,
				'order' => $obj,
				'shipping_place' => $shipping_place,
				'data' => $data
			]);

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

    private function autoGuessFields($order)
    {
        $has_code = false;
        $has_boxes = false;

        foreach($order->products as $product) {
            if (!empty($product->code)) {
                $has_code = true;
            }

            if ($product->package_size != 0) {
                $has_boxes = true;
            }

            if ($has_code && $has_boxes) {
                break;
            }
        }

        $guessed_fields = [];

        if ($has_code) {
            $guessed_fields[] = 'code';
        }

        $guessed_fields[] = 'name';
        $guessed_fields[] = 'quantity';

        if ($has_boxes) {
            $guessed_fields[] = 'boxes';
        }

        $guessed_fields[] = 'measure';
        $guessed_fields[] = 'unit_price';
        $guessed_fields[] = 'price';

        return $guessed_fields;
    }

    protected function handleSummary($obj, $request)
    {
        $send_mail = isset($request['send_mail']);
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'];

        $required_fields = $request['fields'] ?? [];
        if (empty($required_fields)) {
            $required_fields = $this->autoGuessFields($obj);
        }

        $shipping_place = $request['shipping_place'] ?? 'no';
        if ($shipping_place == 'no') {
            $shipping_place = null;
        }

        $data = $obj->formatSummary($required_fields, $status, $shipping_place);
        $title = _i('Prodotti ordine %s presso %s', [$obj->internal_number, $obj->supplier->name]);
        $filename = sanitizeFilename($title . '.' . $subtype);
        $temp_file_path = sprintf('%s/%s', gas_storage_path('temp', true), $filename);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_summary_pdf', [
				'order' => $obj,
				'shipping_place' => $shipping_place,
				'blocks' => [$data]
			]);

            if ($send_mail) {
                $pdf->save($temp_file_path);
            }
            else {
                return $pdf->download($filename);
            }
        }
        else if ($subtype == 'csv') {
            if ($send_mail) {
                output_csv($filename, $data->headers, $data->contents, function($row) {
                    return $row;
                }, $temp_file_path);
            }
            else {
                return output_csv($filename, $data->headers, $data->contents, function($row) {
                    return $row;
                });
            }
        }
        else if ($subtype == 'gdxp') {
            $contents = view('gdxp.json.supplier', ['obj' => $obj->supplier, 'order' => $obj, 'bookings' => true])->render();

            if ($send_mail) {
                file_put_contents($temp_file_path, $contents);
            }
            else {
                download_headers('application/json', $filename);
                return $contents;
            }
        }

        if ($send_mail) {
            $this->sendDocumentMail($request, $temp_file_path);
            return $temp_file_path;
        }
    }

    private function formatTableRows($order, $shipping_place, $status, $fields, &$all_products)
    {
        $bookings = $this->orderTopBookingsByShipping($order, $shipping_place, $status == 'saved' ? 'saved' : null);
		list($get_total, $get_function) = $this->bookingsRules($status);

        $data = [];
        $total_price = 0;

        foreach($bookings as $booking) {
            $row = UserFormatter::format($booking->user, $fields->user_columns);
			$subrow = $this->formatBookingInTable($order, $booking, $status, $all_products);
			$row = array_merge($row, $subrow);

            $price = $booking->getValue($get_total, true);
            $total_price += $price;
            $row[] = printablePrice($price);

            $data[] = $row;
        }

        return [$data, $total_price];
    }

    protected function handleTable($obj, $request)
    {
        $send_mail = isset($request['send_mail']);
        $status = $request['status'] ?? 'booked';
        $shipping_place = $request['shipping_place'] ?? 0;

        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

        $user = Auth::user();
		$obj->setRelation('products', $obj->products()->sorted()->get());

        /*
            Formatto riga di intestazione
        */

        $user_columns = UserFormatter::getHeaders($fields->user_columns);
        list($all_products, $headers, $prices_rows) = $this->formatTableHead($user_columns, [$obj]);

        /*
            Formatto righe delle singole prenotazioni
        */

        list($data, $total_price) = $this->formatTableRows($obj, $shipping_place, $status, $fields, $all_products);
        array_unshift($data, $prices_rows);

        /*
            Formatto riga dei totali
        */

		$row = $this->formatTableFooter([$obj], $user_columns, $all_products, $total_price);
        $data[] = $row;
        $data[] = $headers;

        /*
            Genero documento
        */

        $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$obj->internal_number, $obj->supplier->name]));

        if ($send_mail) {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);
            $this->sendDocumentMail($request, $temp_file_path);
        }
        else {
            return output_csv($filename, $headers, $data, null);
        }
    }
}
