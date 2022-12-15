<?php

namespace App\Printers;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App;
use PDF;
use ezcArchive;

class Aggregate extends Printer
{
    /*
        Prima di invocare questa funzione, si assume che il GAS corrente sia già
        stato settato in GlobalScopeHub
    */
    private function formatGasSummary($gas, $aggregate, $required_fields, $status, $shipping_place)
    {
        $data = (object) [
            'title' => $gas ? $gas->name : _i('Complessivo'),
            'headers' => [],
            'contents' => [],
        ];

        foreach($aggregate->orders as $order) {
            $temp_data = $order->formatSummary($required_fields, $status, $shipping_place);
            $data->headers = $temp_data->headers;
            $data->contents = array_merge($data->contents, $temp_data->contents);
        }

        return $data;
    }

    private function handleShipping($obj, $request)
    {
        $subtype = $request['format'] ?? 'pdf';
        $required_fields = $request['fields'] ?? [];

        $fields = splitFields($required_fields);
        $status = $request['status'] ?? 'booked';
        $shipping_place = $request['shipping_place'] ?? 'all_by_name';

        $temp_data = [];
        foreach($obj->orders as $order) {
            $temp_data[] = $order->formatShipping($fields, $status, $shipping_place, true);
        }

        if (empty($temp_data)) {
            $data = (object) [
                'headers' => [],
                'contents' => []
            ];
        }
        else {
            $data = (object) [
                'headers' => $temp_data[0]->headers,
                'contents' => []
            ];

            foreach($temp_data as $td_row) {
                foreach($td_row->contents as $td) {
                    $found = false;

                    foreach($data->contents as $d) {
                        if ($d->user_id == $td->user_id) {
                            $d->products = array_merge($d->products, $td->products);
                            $d->notes = array_merge($d->notes, $td->notes);

                            /*
                                Nell'array "totals" si trova il totale della
                                prenotazione, ma anche i totali dei modificatori
                            */
                            foreach($td->totals as $index => $t) {
                                $d->totals[$index] = ($d->totals[$index] ?? 0) + $td->totals[$index];
                            }

                            $found = true;
                            break;
                        }
                    }

                    if ($found == false) {
                        $data->contents[] = $td;
                    }
                }
            }

            $all_gas = (App::make('GlobalScopeHub')->enabled() == false);

            usort($data->contents, function($a, $b) use ($shipping_place, $all_gas) {
                if ($shipping_place == 'all_by_place' && $a->shipping_sorting != $b->shipping_sorting) {
                    return $a->shipping_sorting <=> $b->shipping_sorting;
                }

                if ($all_gas) {
                    return $a->gas_sorting <=> $b->gas_sorting;
                }

                return $a->user_sorting <=> $b->user_sorting;
            });
        }

        $title = _i('Dettaglio Consegne Ordini');
        $filename = sanitizeFilename($title . '.' . $subtype);

        if ($subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', ['fields' => $fields, 'aggregate' => $obj, 'data' => $data]);
            enablePdfPagesNumbers($pdf);
            return $pdf->download($filename);
        }
        else if ($subtype == 'csv') {
            $flat_contents = [];

            foreach($data->contents as $c) {
                foreach($c->products as $p) {
                    $flat_contents[] = array_merge($c->user, $p);
                }
            }

            return output_csv($filename, $data->headers, $flat_contents, function($row) {
                return $row;
            });
        }
    }

    private function handleGDXP($obj)
    {
        $hub = App::make('GlobalScopeHub');
        if ($hub->enabled() == false) {
            $gas = $obj->gas->pluck('id');
        }
        else {
            $gas = Arr::wrap($hub->getGas());
        }

        $working_dir = sys_get_temp_dir();
        chdir($working_dir);

        $files = [];
        $printer = new Order();

        foreach($gas as $g) {
            $hub->enable(true);
            $hub->setGas($g);

            foreach($obj->orders as $order) {
                /*
                    Attenzione: la funzione document() nomina il
                    file sempre nello stesso modo, a prescindere dal
                    GAS. Se non lo si rinomina in altro modo, le
                    diverse iterazioni sovrascrivono sempre lo
                    stesso file
                */
                $f = $printer->document($order, 'summary', ['format' => 'gdxp', 'status' => 'booked']);
                $new_f = Str::random(10);
                rename($f, $new_f);
                $files[] = $new_f;
            }
        }

        $archivepath = sprintf('%s/prenotazioni.zip', $working_dir);
        $archive = ezcArchive::open($archivepath, ezcArchive::ZIP);

        foreach($files as $f) {
            $archive->append([$f], '');
            unlink($f);
        }

        return response()->download($archivepath)->deleteFileAfterSend(true);
    }

    private function handleSummary($obj, $request)
    {
        $subtype = $request['format'] ?? 'pdf';

        if ($subtype == 'pdf' || $subtype == 'csv') {
            $required_fields = $request['fields'] ?? [];
            $status = $request['status'];

            $shipping_place = $request['shipping_place'] ?? 'all_by_place';
            if ($shipping_place == 'all_by_place') {
                $shipping_place = null;
            }

            $data = null;
            $title = _i('Prodotti Ordini');
            $filename = sanitizeFilename($title . '.' . $subtype);

            if ($subtype == 'pdf') {
                $blocks = [];

                $hub = App::make('GlobalScopeHub');
                if ($hub->enabled() == false) {
                    $gas = $obj->gas->pluck('id');
                    $blocks[] = $this->formatGasSummary(null, $obj, $required_fields, $status, $shipping_place);
                }
                else {
                    $gas = Arr::wrap($hub->getGas());
                }

                foreach($gas as $g) {
                    $hub->enable(true);
                    $hub->setGas($g);
                    $blocks[] = $this->formatGasSummary($hub->getGasObj(), $obj, $required_fields, $status, $shipping_place);
                }

                $pdf = PDF::loadView('documents.order_summary_pdf', ['aggregate' => $obj, 'blocks' => $blocks]);
                return $pdf->download($filename);
            }
            else if ($subtype == 'csv') {
                foreach($obj->orders as $order) {
                    $temp_data = $order->formatSummary($required_fields, $status, $shipping_place);
                    if (is_null($data)) {
                        $data = $temp_data;
                    }
                    else {
                        $data->contents = array_merge($data->contents, $temp_data->contents);
                    }
                }

                return output_csv($filename, $data->headers, $data->contents, function($row) {
                    return $row;
                });
            }
        }
        else if ($subtype == 'gdxp') {
            return $this->handleGDXP($obj);
        }
    }

    public function document($obj, $type, $request)
    {
        switch ($type) {
            case 'shipping':
                return $this->handleShipping($obj, $request);

            case 'summary':
                return $this->handleSummary($obj, $request);

            default:
                \Log::error('Unrecognized type for Aggregate document: ' . $type);
                return null;
        }
    }
}
