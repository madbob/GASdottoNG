<?php

namespace App\Printers;

use PDF;
use Mail;

use App\Booking;
use App\Notifications\GenericOrderShipping;

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

        $real_recipient_mails = array_map(array_filter($recipient_mails), function($item) {
            return (object) ['email' => $item];
        });

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
        $status = $request['status'];
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

    private function handleTable($obj, $request)
    {
        $status = $request['status'] ?? 'booked';
        $shipping_place = $request['shipping_place'] ?? 0;

        if ($status == 'booked' || $status == 'delivered') {
            $bookings = $this->orderTopBookingsByShipping($obj, $shipping_place, null);
        }
        else if ($status == 'saved') {
            $bookings = $this->orderTopBookingsByShipping($obj, $shipping_place, 'saved');
        }

        $contents = view('documents.order_table_' . $status, ['order' => $obj, 'bookings' => $bookings])->render();
        $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$obj->internal_number, $obj->supplier->name]));
        return output_csv($filename, null, $contents, null, null);
    }

    public function document($obj, $type, $request)
    {
        switch ($type) {
            case 'shipping':
                return $this->handleShipping($obj, $request);
                break;

            case 'summary':
                return $this->handleSummary($obj, $request);
                break;

            case 'table':
                return $this->handleTable($obj, $request);
                break;

            default:
                \Log::error('Unrecognized type for Order document: ' . $type);
                return null;
                break;
        }
    }
}
