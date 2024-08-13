<?php

namespace App\Printers;

use Auth;
use PDF;
use Mail;

use App\Printers\Concerns\Orders;
use App\Formatters\User as UserFormatter;
use App\Notifications\GenericOrderShipping;
use App\Printers\Components\Document;
use App\Printers\Components\Table;
use App\Printers\Components\Title;
use App\Printers\Components\Header;
use App\Booking;
use App\Delivery;

class Order extends Printer
{
	use Orders;

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

		try {
        	Mail::to($real_recipient_mails)->send(new GenericOrderShipping($temp_file_path, $request['subject_mail'], $request['body_mail']));
		}
		catch(\Exception $e) {
			\Log::error('Impossibile inoltrare documento ordine: ' . $e->getMessage());
		}

        @unlink($temp_file_path);
    }

    protected function handleShipping($obj, $request)
    {
		$action = $request['action'] ?? 'download';
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'] ?? 'pending';
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

        $data = $this->formatShipping($obj, $fields, $status, $shipping_place, $extra_modifiers);

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

            if (in_array($action, ['save', 'email'])) {
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
            }

            if (in_array($action, ['save', 'email'])) {
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

        if ($action == 'email') {
            $this->sendDocumentMail($request, $temp_file_path);
        }
		else if ($action == 'save') {
			return $temp_file_path;
		}
    }

    private function autoGuessFields($order)
    {
		$guessed_fields = [];

		if ($order->products->filter(fn($p) => empty($p->code) == false)->count() != 0) {
            $guessed_fields[] = 'code';
		}

		$guessed_fields[] = 'name';
        $guessed_fields[] = 'quantity';

		if ($order->products->filter(fn($p) => $p->package_size != 0)->count() != 0) {
            $guessed_fields[] = 'boxes';
        }

        $guessed_fields[] = 'measure';
        $guessed_fields[] = 'unit_price';
        $guessed_fields[] = 'price';

        return $guessed_fields;
    }

    protected function handleSummary($obj, $request)
    {
        $action = $request['action'] ?? 'download';
        $subtype = $request['format'] ?? 'pdf';
		$extra_modifiers = $request['extra_modifiers'] ?? 0;

        $title = _i('Prodotti ordine %s presso %s', [$obj->internal_number, $obj->supplier->name]);
        $filename = sanitizeFilename($title . '.' . $subtype);
        $temp_file_path = sprintf('%s/%s', gas_storage_path('temp', true), $filename);

		if ($subtype == 'gdxp') {
            $contents = view('gdxp.json.supplier', ['obj' => $obj->supplier, 'order' => $obj, 'bookings' => true])->render();

			if ($action == 'email') {
				file_put_contents($temp_file_path, $contents);
	            $this->sendDocumentMail($request, $temp_file_path);
	            return $temp_file_path;
	        }
			else if ($action == 'download') {
				download_headers('application/json', $filename);
				return $contents;
			}
			else if ($action == 'save') {
				file_put_contents($temp_file_path, $contents);
				return $temp_file_path;
			}
        }
		else {
			$status = $request['status'];

	        $required_fields = $request['fields'] ?? [];
	        if (empty($required_fields)) {
	            $required_fields = $this->autoGuessFields($obj);
	        }

	        $shipping_place = $request['shipping_place'] ?? 'no';
	        if ($shipping_place == 'no') {
	            $shipping_place = null;
	        }

			$document = new Document($subtype);

			$document_title = _i('Prodotti ordine %s presso %s del %s', [
				$obj->internal_number,
				$obj->supplier->printableName(),
				$obj->shipping ? date('d/m/Y', strtotime($obj->shipping)) : date('d/m/Y')
			]);

			$document->append(new Title($document_title));

	        $document = $this->formatSummary($obj, $document, $required_fields, $status, $shipping_place, $extra_modifiers);

			if ($action == 'email') {
				$document->save($temp_file_path);
	            $this->sendDocumentMail($request, $temp_file_path);
	            return $temp_file_path;
	        }
			else if ($action == 'download') {
				return $document->download($filename);
			}
			else if ($action == 'save') {
				$document->save($temp_file_path);
				return $temp_file_path;
			}
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
        $action = $request['action'] ?? 'download';
        $status = $request['status'] ?? 'pending';
		$include_missing = $request['include_missing'] ?? 'no';
        $shipping_place = $request['shipping_place'] ?? 0;

        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

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

		if ($include_missing == 'no') {
			$data = $this->compressTable($user_columns, $data);
			$headers = $data[count($data) - 1];
		}

        /*
            Genero documento
        */

        $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$obj->internal_number, $obj->supplier->name]));

        if ($action == 'email') {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);
            $this->sendDocumentMail($request, $temp_file_path);
        }
        else if ($action == 'download') {
            return output_csv($filename, $headers, $data, null);
        }
		else if ($action == 'save') {
			$temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);
            return $temp_file_path;
		}
    }
}
