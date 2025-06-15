<?php

namespace App\Printers;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use PDF;

use App\Helpers\CirclesFilter;
use App\Printers\Concerns\Orders;
use App\Printers\Components\Document;
use App\Printers\Components\Header;
use App\Printers\Components\Title;
use App\Formatters\User as UserFormatter;

class Aggregate extends Printer
{
    use Orders;

    protected function handleShipping($obj, $request)
    {
        $subtype = $request['format'] ?? 'pdf';
        $required_fields = $request['fields'] ?? [];

        $fields = splitFields($required_fields);
        $status = $request['status'] ?? 'pending';
        $circles = new CirclesFilter($obj, $request);
        $isolate_friends = $request['isolate_friends'] ?? 0;

        $temp_data = [];
        foreach ($obj->orders as $order) {
            $temp_data[] = $this->formatShipping($order, $fields, $status, $isolate_friends, $circles, true);
        }

        if (empty($temp_data)) {
            $data = (object) [
                'headers' => [],
                'contents' => [],
            ];
        }
        else {
            $data = (object) [
                'headers' => $temp_data[0]->headers,
                'contents' => [],
            ];

            foreach ($temp_data as $td_row) {
                foreach ($td_row->contents as $td) {
                    $found = false;

                    foreach ($data->contents as $d) {
                        if ($d->user_id == $td->user_id) {
                            $d->products = array_merge($d->products, $td->products);
                            $d->notes = array_merge($d->notes, $td->notes);

                            /*
                                Nell'array "totals" si trova il totale della
                                prenotazione, ma anche i totali dei modificatori
                            */
                            foreach ($td->totals as $index => $t) {
                                $d->totals[$index] = ($d->totals[$index] ?? 0) + $t;
                            }

                            $found = true;
                            break;
                        }
                    }

                    if ($found === false) {
                        $data->contents[] = $td;
                    }
                }
            }

            $all_gas = (App::make('GlobalScopeHub')->enabled() === false);

            /*
                Attenzione: in $data->contents non ci sono istanze di Booking,
                dunque non posso usare qui CirclesFilter::sortBookings()
            */
            usort($data->contents, function ($a, $b) use ($circles, $all_gas) {
                if ($circles->sortedByUser() === false && $a->circles_sorting != $b->circles_sorting) {
                    return $a->circles_sorting <=> $b->circles_sorting;
                }

                if ($all_gas) {
                    return $a->gas_sorting <=> $b->gas_sorting;
                }

                return $a->user_sorting <=> $b->user_sorting;
            });
        }

        $title = __('orders.files.order.shipping');
        $filename = sanitizeFilename($title . '.' . $subtype);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', [
                'fields' => $fields,
                'aggregate' => $obj,
                'circles' => $circles,
                'data' => $data,
            ]);

            enablePdfPagesNumbers($pdf);

            return $pdf->download($filename);
        }
        elseif ($subtype == 'csv') {
            $flat_contents = [];

            foreach ($data->contents as $c) {
                foreach ($c->products as $p) {
                    $flat_contents[] = array_merge($c->user, $p);
                }
            }

            return output_csv($filename, $data->headers, $flat_contents, function ($row) {
                return $row;
            });
        }
    }

    private function handleGDXP($obj)
    {
        $hub = App::make('GlobalScopeHub');
        if ($hub->enabled() === false) {
            $gas = $obj->gas->pluck('id');
        }
        else {
            $gas = Arr::wrap($hub->getGas());
        }

        $working_dir = sys_get_temp_dir();
        chdir($working_dir);

        $files = [];
        $printer = new Order();

        foreach ($gas as $g) {
            $hub->enable(true);
            $hub->setGas($g);

            foreach ($obj->orders as $order) {
                /*
                    Attenzione: la funzione document() nomina il
                    file sempre nello stesso modo, a prescindere dal
                    GAS. Se non lo si rinomina in altro modo, le
                    diverse iterazioni sovrascrivono sempre lo
                    stesso file
                */
                $f = $printer->document($order, 'summary', ['format' => 'gdxp', 'status' => 'pending']);
                $new_f = Str::random(10);
                rename($f, $new_f);
                $files[] = $new_f;
            }
        }

        $archivepath = sprintf('%s/prenotazioni.zip', $working_dir);
        zipAll($archivepath, $files);

        return response()->download($archivepath)->deleteFileAfterSend(true);
    }

    protected function handleSummary($obj, $request)
    {
        $subtype = $request['format'] ?? 'pdf';

        if ($subtype == 'gdxp') {
            return $this->handleGDXP($obj);
        }
        else {
            $required_fields = $request['fields'] ?? [];
            $status = $request['status'];
            $circles = new CirclesFilter($obj, $request);

            $document = new Document($subtype);

            $document_title = __('products.list') . '<br>';
            if ($obj->orders->count() <= aggregatesConvenienceLimit()) {
                foreach ($obj->orders as $order) {
                    $document_title .= sprintf('%s %s<br>', $order->supplier->name, $order->internal_number);
                }
            }

            $document->append(new Title($document_title));

            $hub = App::make('GlobalScopeHub');
            if ($hub->enabled() === false) {
                $gas = $obj->gas;
            }
            else {
                $gas = Arr::wrap($hub->getGasObj());
            }

            foreach ($gas as $g) {
                $hub->enable(true);
                $hub->setGas($g);

                foreach ($obj->orders as $order) {
                    $document->append(new Header($order->printableName()));
                    $document = $this->formatSummary($order, $document, $required_fields, $status, $circles, false);
                }
            }

            $title = __('products.list');
            $filename = sanitizeFilename($title . '.' . $subtype);

            return $document->download($filename);
        }
    }

    private function orderTopBookingsByShipping($aggregate, $circles, $status = null)
    {
        $bookings = $aggregate->bookings;

        if ($status) {
            $bookings = $bookings->where('status', $status);
        }

        return $circles->sortBookings($bookings);
    }

    private function formatTableRows($aggregate, $circles, $status, $fields, &$all_products)
    {
        $bookings = $this->orderTopBookingsByShipping($aggregate, $circles, $status == 'saved' ? 'saved' : null);
        [$get_total, $get_function] = $this->bookingsRules($status);

        $data = [];
        $total_price = 0;

        foreach ($bookings as $booking) {
            $row = UserFormatter::format($booking->user, $fields->user_columns);

            foreach ($aggregate->orders as $order) {
                $sub_booking = $booking->getOrderBooking($order);
                $subrow = $this->formatBookingInTable($order, $sub_booking, $status, $all_products);
                $row = array_merge($row, $subrow);
            }

            $price = $booking->getValue($get_total, true);
            $total_price += $price;
            $row[] = printablePrice($price);

            $data[] = $row;
        }

        return [$data, $total_price];
    }

    protected function handleTable($obj, $request)
    {
        $status = $request['status'] ?? 'pending';
        $include_missing = $request['include_missing'] ?? 'no';
        $circles = $request['circles'] ?? ['no'];

        $required_fields = $request['fields'] ?? [];
        $fields = splitFields($required_fields);

        /*
            Formatto riga di intestazione
        */

        $user_columns = UserFormatter::getHeaders($fields->user_columns);
        [$all_products, $headers, $prices_rows] = $this->formatTableHead($user_columns, $obj->orders);

        /*
            Formatto righe delle singole prenotazioni
        */

        [$data, $total_price] = $this->formatTableRows($obj, $circles, $status, $fields, $all_products);
        array_unshift($data, $prices_rows);

        /*
            Formatto riga dei totali
        */

        $row = $this->formatTableFooter($obj->orders, $user_columns, $all_products, $total_price);
        $data[] = $row;
        $data[] = $headers;

        if ($include_missing == 'no') {
            $data = $this->compressTable($user_columns, $data);
            $headers = $data[count($data) - 1];
        }

        /*
            Genero documento
        */

        $filename = __('orders.files.order.table');
        $filename = sanitizeFilename($filename . '.csv');

        return output_csv($filename, $headers, $data, null);
    }
}
