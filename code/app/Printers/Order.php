<?php

namespace App\Printers;

use PDF;

use App\Helpers\CirclesFilter;
use App\Printers\Concerns\Orders;
use App\Formatters\User as UserFormatter;
use App\Printers\Components\Document;
use App\Printers\Components\Title;

class Order extends Printer
{
    use Orders;

    /*************************************************************** Shipping */

    /*
        Se extra_modifiers == false (o non definito affatto): non contempla i
        modificatori che hanno un tipo movimento contabile esplicito (e dunque
        non sono destinati al fonitore)
    */
    protected function handleShipping($obj, $request)
    {
        $params = new PrintParams($request, $obj);
        $circles = new CirclesFilter($obj->aggregate, $request);

        $data = $this->formatShipping($obj, $params, $circles);

        $title = __('texts.orders.documents.shipping.heading', [
            'identifier' => $obj->internal_number,
            'supplier' => $obj->supplier->name,
            'date' => $obj->shipping ? date('d/m/Y', strtotime($obj->shipping)) : date('d/m/Y'),
        ]);

        $filename = sanitizeFilename($title . '.' . $params->subtype);

        if ($params->subtype == 'pdf') {
            $pdf = PDF::loadView('documents.order_shipping_pdf', [
                'fields' => $params->fields,
                'order' => $obj,
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

    protected function handleSummary($obj, $request)
    {
        $params = new PrintParams($request, $obj);

        $title = __('texts.orders.documents.summary.heading', [
            'identifier' => $obj->internal_number,
            'supplier' => $obj->supplier->name,
        ]);

        $filename = sanitizeFilename($title . '.' . $params->subtype);

        if ($params->subtype == 'gdxp') {
            $contents = view('gdxp.json.supplier', ['obj' => $obj->supplier, 'order' => $obj, 'bookings' => true])->render();
            $temp_file_path = sprintf('%s/%s', gas_storage_path('temp', true), $filename);

            if ($params->action == 'email') {
                file_put_contents($temp_file_path, $contents);
                $this->sendDocumentMail($request, $temp_file_path);

                return $temp_file_path;
            }
            elseif ($params->action == 'download') {
                download_headers('application/json', $filename);

                return $contents;
            }
            elseif ($params->action == 'save') {
                file_put_contents($temp_file_path, $contents);

                return $temp_file_path;
            }
        }
        else {
            $circles = new CirclesFilter($obj->aggregate, $request);
            $document = new Document($params->subtype);

            $document_title = __('texts.orders.documents.summary.heading', [
                'identifier' => $obj->internal_number,
                'supplier' => $obj->supplier->printableName(),
            ]);

            $document->append(new Title($document_title));

            $document = $this->formatSummary($obj, $document, $params, $circles);
            return $this->outputPdf($params, $filename, $document);
        }
    }

    /****************************************************************** Table */

    protected function getFormattableDataForTable($master, $params)
    {
        $circles = new CirclesFilter($master->aggregate, $params->request);
        $bookings = $master->topLevelBookings($params->status == 'saved' ? 'saved' : null);
        $bookings = $circles->sortBookings($bookings);

        $orders = [$master];

        return [$orders, $bookings];
    }

    protected function handleTable($obj, $request)
    {
        return $this->realHandleTable($obj, $obj->aggregate, [$obj], $request);
    }
}
