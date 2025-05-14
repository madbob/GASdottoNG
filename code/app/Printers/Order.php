<?php

namespace App\Printers;

use Illuminate\Support\Facades\Mail;
use PDF;

use App\Helpers\CirclesFilter;
use App\Printers\Concerns\Orders;
use App\Formatters\User as UserFormatter;
use App\Notifications\GenericOrderShipping;
use App\Printers\Components\Document;
use App\Printers\Components\Title;

class Order extends Printer
{
    use Orders;

    private function sendDocumentMail($request, $temp_file_path)
    {
        $recipient_mails = $request['recipient_mail_value'] ?? [];

        $real_recipient_mails = array_map(function ($item) {
            return (object) ['email' => $item];
        }, array_filter($recipient_mails));

        if (empty($real_recipient_mails)) {
            return;
        }

        try {
            Mail::to($real_recipient_mails)->send(new GenericOrderShipping($temp_file_path, $request['subject_mail'], $request['body_mail']));
        }
        catch (\Exception $e) {
            \Log::error('Impossibile inoltrare documento ordine: ' . $e->getMessage());
        }

        @unlink($temp_file_path);
    }

    /*
        Se extra_modifiers == false (o non definito affatto): non contempla i
        modificatori che hanno un tipo movimento contabile esplicito (e dunque
        non sono destinati al fonitore)
    */
    protected function handleShipping($obj, $request)
    {
        $action = $request['action'] ?? 'download';
        $subtype = $request['format'] ?? 'pdf';
        $status = $request['status'] ?? 'pending';
        $isolate_friends = ($request['isolate_friends'] ?? 0) == 1;
        $extra_modifiers = ($request['extra_modifiers'] ?? 0) == 1;
        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);
        $circles = new CirclesFilter($obj->aggregate, $request);

        $data = $this->formatShipping($obj, $fields, $status, $isolate_friends, $circles, $extra_modifiers);

        $title = _i('Dettaglio Consegne ordine %s presso %s', [$obj->internal_number, $obj->supplier->name]);
        $filename = sanitizeFilename($title . '.' . $subtype);
        $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', [
                'fields' => $fields,
                'order' => $obj,
                'circles' => $circles,
                'data' => $data,
            ]);

            enablePdfPagesNumbers($pdf);

            if (in_array($action, ['save', 'email'])) {
                $pdf->save($temp_file_path);
            }
            else {
                return $pdf->download($filename);
            }
        }
        elseif ($subtype == 'csv') {
            $flat_contents = [];

            foreach ($data->contents as $c) {
                foreach ($c->products as $p) {
                    $flat_contents[] = array_merge($c->user, $p);
                }
            }

            if (in_array($action, ['save', 'email'])) {
                output_csv($filename, $data->headers, $flat_contents, function ($row) {
                    return $row;
                }, $temp_file_path);
            }
            else {
                return output_csv($filename, $data->headers, $flat_contents, function ($row) {
                    return $row;
                });
            }
        }

        if ($action == 'email') {
            $this->sendDocumentMail($request, $temp_file_path);
        }
        elseif ($action == 'save') {
            return $temp_file_path;
        }
    }

    private function autoGuessFields($order)
    {
        $guessed_fields = [];

        if ($order->products->filter(fn ($p) => empty($p->code) === false)->count() != 0) {
            $guessed_fields[] = 'code';
        }

        $guessed_fields[] = 'name';
        $guessed_fields[] = 'quantity';

        if ($order->products->filter(fn ($p) => $p->package_size != 0)->count() != 0) {
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
        $extra_modifiers = ($request['extra_modifiers'] ?? 0) == 1;

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
            elseif ($action == 'download') {
                download_headers('application/json', $filename);

                return $contents;
            }
            elseif ($action == 'save') {
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

            $circles = new CirclesFilter($obj->aggregate, $request);

            $document = new Document($subtype);

            $document_title = _i('Prodotti ordine %s presso %s del %s', [
                $obj->internal_number,
                $obj->supplier->printableName(),
                $obj->shipping ? $obj->shipping->format('d/m/Y') : date('d/m/Y'),
            ]);

            $document->append(new Title($document_title));

            $document = $this->formatSummary($obj, $document, $required_fields, $status, $circles, $extra_modifiers);

            if ($action == 'email') {
                $document->save($temp_file_path);
                $this->sendDocumentMail($request, $temp_file_path);

                return $temp_file_path;
            }
            elseif ($action == 'download') {
                return $document->download($filename);
            }
            elseif ($action == 'save') {
                $document->save($temp_file_path);

                return $temp_file_path;
            }
        }
    }

    private function formatTableRows($order, $circles, $status, $fields, &$all_products)
    {
        $bookings = $order->topLevelBookings($status == 'saved' ? 'saved' : null);
        $bookings = $circles->sortBookings($bookings);

        [$get_total, $get_function] = $this->bookingsRules($status);

        $data = [];
        $total_price = 0;

        foreach ($bookings as $booking) {
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
        $circles = new CirclesFilter($obj->aggregate, $request);

        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

        $obj->setRelation('products', $obj->products()->sorted()->get());

        /*
            Formatto riga di intestazione
        */

        $user_columns = UserFormatter::getHeaders($fields->user_columns);
        [$all_products, $headers, $prices_rows] = $this->formatTableHead($user_columns, [$obj]);

        /*
            Formatto righe delle singole prenotazioni
        */

        [$data, $total_price] = $this->formatTableRows($obj, $circles, $status, $fields, $all_products);
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
        elseif ($action == 'download') {
            return output_csv($filename, $headers, $data, null);
        }
        elseif ($action == 'save') {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);

            return $temp_file_path;
        }
    }
}
