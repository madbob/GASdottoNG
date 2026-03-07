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

class Aggregate extends Printer
{
    use Orders;

    /*************************************************************** Shipping */

    protected function handleShipping($obj, $request)
    {
        $params = new PrintParams($request, $obj);
        $circles = new CirclesFilter($obj, $request);

        $temp_data = [];
        foreach ($obj->orders as $order) {
            if ($circles->getMode() == 'all_by_place') {
                foreach ($circles->combinations() as $combo) {
                    $temp_data[] = $this->formatShipping($order, $params, $combo);
                }
            }
            else {
                $temp_data[] = $this->formatShipping($order, $params, $circles);
            }
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

        $title = __('texts.orders.files.order.shipping');
        $filename = sanitizeFilename($title . '.' . $params->subtype);

        if ($params->subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', [
                'fields' => $params->fields,
                'aggregate' => $obj,
                'circles' => $circles,
                'data' => $data,
            ]);

            enablePdfPagesNumbers($pdf);

            return $this->outputPdf($params, $filename, $pdf);
        }
        elseif ($params->subtype == 'csv') {
            $flat_contents = [];

            foreach ($data->contents as $c) {
                foreach ($c->products as $p) {
                    $flat_contents[] = array_merge($c->user, $p);
                }
            }

            return $this->outputCsv($params, $filename, $data->headers, $flat_contents);
        }
    }

    /**************************************************************** Summary */

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
        $params = new PrintParams($request, $obj);

        if ($params->subtype == 'gdxp') {
            return $this->handleGDXP($obj);
        }
        else {
            $circles = new CirclesFilter($obj, $request);
            $document = new Document($params->subtype);

            $document_title = __('texts.products.list') . '<br>';
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
                    $document = $this->formatSummary($order, $document, $params, $circles);
                }
            }

            $title = __('texts.products.list');
            $filename = sanitizeFilename($title . '.' . $params->subtype);
            return $this->outputPdf($params, $filename, $document);
        }
    }

    /****************************************************************** Table */

    protected function getFormattableDataForTable($master, $params)
    {
        $bookings = $master->bookings;

        if ($params->status == 'saved') {
            $bookings = $bookings->where('status', $params->status);
        }

        $circles = new CirclesFilter($master, $params->request);
        $bookings = $circles->sortBookings($bookings);

        $orders = $master->orders;
        return [$orders, $bookings];
    }

    protected function handleTable($obj, $request)
    {
        return $this->realHandleTable($obj, $obj, $obj->orders, $request);
    }
}
