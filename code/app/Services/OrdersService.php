<?php

namespace App\Services;

use Illuminate\Support\Arr;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use PDF;
use Mail;
use DB;

use App\Order;
use App\Aggregate;
use App\Supplier;
use App\Booking;
use App\Notifications\GenericOrderShipping;

class OrdersService extends BaseService
{
    public function show($id, $edit = false)
    {
        $ret = Order::findOrFail($id);

        if ($edit) {
            $this->ensureAuth(['supplier.orders' => $ret->supplier]);
        }

        return $ret;
    }

    private function setCommonAttributes($order, $request)
    {
        $this->setIfSet($order, $request, 'comment');
        $this->transformAndSetIfSet($order, $request, 'start', 'decodeDate');
        $this->transformAndSetIfSet($order, $request, 'end', 'decodeDate');
        $this->transformAndSetIfSet($order, $request, 'shipping', 'decodeDate');
        $this->setIfSet($order, $request, 'keep_open_packages');
        return $order;
    }

    public function store(array $request)
    {
        DB::beginTransaction();

        $a = new Aggregate();
        $suppliers = Arr::wrap($request['supplier_id']);

        if (count($suppliers) > 1) {
            $a->comment = $request['comment'] ?? '';
            $request['comment'] = '';
        }

        $a->save();

        $deliveries = array_filter($request['deliveries'] ?? []);
        $request['keep_open_packages'] = $request['keep_open_packages'] ?? 'no';

        foreach($suppliers as $supplier_id) {
            $supplier = Supplier::findOrFail($supplier_id);
            $this->ensureAuth(['supplier.orders' => $supplier]);

            $o = new Order();
            $o->supplier_id = $supplier->id;

            $this->setCommonAttributes($o, $request);
            $o->status = $request['status'];
            $o->aggregate_id = $a->id;
            $o->save();

            $o->deliveries()->sync($deliveries);
        }

        return $a;
    }

    public function update($id, array $request)
    {
        DB::beginTransaction();

        $order = $this->show($id, true);
        $this->setCommonAttributes($order, $request);
        $order->deliveries()->sync(array_filter($request['deliveries'] ?? []));
        $order->users()->sync($request['users'] ?? []);

        /*
            Se un ordine viene riaperto, modifico artificiosamente la sua data
            di chiusura. Questo per evitare che venga nuovamente automaticamente
            chiuso
        */
        $status = $request['status'];
        if ($order->status != $status) {
            $today = date('Y-m-d');
            if ($status == 'open' && $order->end < $today) {
                $order->end = $today;
            }

            $order->status = $status;
        }

        $order->save();

        /*
            Se vengono rimossi dei prodotti dall'ordine, ne elimino tutte le
            relative prenotazioni sinora avvenute
        */
        $enabled = $request['enabled'] ?? [];
        $removed_products = $order->products()->whereNotIn('id', $enabled)->pluck('id')->toArray();
        if (!empty($removed_products)) {
            foreach($order->bookings as $booking) {
                $booking->products()->whereIn('product_id', $removed_products)->delete();
                if ($booking->products->isEmpty()) {
                    $booking->delete();
                }
            }
        }

        $order->products()->sync($enabled);
        return $order->aggregate;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        $order = $this->show($id, true);
        $order->delete();
        return $order;
    }

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

    public function document($id, $type, $request)
    {
        $order = $this->show($id);

        switch ($type) {
            case 'shipping':
                $send_mail = isset($request['send_mail']);
                $subtype = $request['format'] ?? 'pdf';
                $status = $request['status'];
                $required_fields = $request['fields'] ?? [];
                $fields = splitFields($required_fields);

                $shipping_place = $request['shipping_place'] ?? 'all_by_name';
                $data = $order->formatShipping($fields, $status, $shipping_place);

                $title = _i('Dettaglio Consegne ordine %s presso %s', [$order->internal_number, $order->supplier->name]);
                $filename = sanitizeFilename($title . '.' . $subtype);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($subtype == 'pdf') {
                    $pdf = PDF::loadView('documents.order_shipping_pdf', ['fields' => $fields, 'order' => $order, 'data' => $data]);
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

                break;

            case 'summary':
                $send_mail = isset($request['send_mail']);
                $subtype = $request['format'] ?? 'pdf';
                $status = $request['status'];
                $required_fields = $request['fields'] ?? [];

                $shipping_place = $request['shipping_place'] ?? 'all_by_place';
                if ($shipping_place == 'all_by_place') {
                    $shipping_place = null;
                }

                if ($send_mail) {
                    $temp_file_path = $order->document('summary', $subtype, 'save', $required_fields, $status, $shipping_place);
                    $this->sendDocumentMail($request, $temp_file_path);
                }
                else {
                    return $order->document('summary', $subtype, 'return', $required_fields, $status, $shipping_place);
                }

                break;

            case 'table':
                $status = $request['status'] ?? 'booked';
                $shipping_place = $request['shipping_place'] ?? 0;

                if ($status == 'booked' || $status == 'delivered') {
                    $bookings = $this->orderTopBookingsByShipping($order, $shipping_place, null);
                }
                else if ($status == 'saved') {
                    $bookings = $this->orderTopBookingsByShipping($order, $shipping_place, 'saved');
                }

                $contents = view('documents.order_table_' . $status, ['order' => $order, 'bookings' => $bookings])->render();
                $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$order->internal_number, $order->supplier->name]));
                return output_csv($filename, null, $contents, null, null);
        }
    }
}
