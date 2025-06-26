<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use App\Services\InvoicesService;
use App\Invoice;
use App\Movement;

class InvoicesController extends BackedController
{
    public function __construct(InvoicesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => Invoice::class,
            'service' => $service,
        ]);
    }

    public function index(Request $request)
    {
        $past = date('Y-m-d', strtotime('-1 months'));
        $future = date('Y-m-d', strtotime('+10 years'));
        $invoices = $this->service->list($past, $future, 0);

        return view('invoice.index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Request $request, $id)
    {
        $invoice = $this->service->show($id);
        $user = $request->user();

        if ($user->can('supplier.invoices', $invoice->supplier)) {
            return view('invoice.edit', [
                'invoice' => $invoice,
            ]);
        }
        else {
            return view('invoice.show', [
                'invoice' => $invoice,
            ]);
        }
    }

    public function products($id)
    {
        return view('invoice.products', $this->service->products($id));
    }

    public function orders($id)
    {
        $invoice = $this->service->show($id);

        return view('invoice.orders', [
            'invoice' => $invoice,
        ]);
    }

    private function validMovementTypes()
    {
        $alternative_types = [];
        $available_types = movementTypes();

        foreach ($available_types as $at) {
            if ($at->validForInvoices()) {
                $alternative_types[$at->id] = $at->name;
            }
        }

        return $alternative_types;
    }

    public function getMovements(Request $request, $id)
    {
        $invoice = $this->service->show($id);
        $user = $this->ensureAuth(['movements.admin' => 'gas', 'supplier.movements' => $invoice->supplier]);

        $invoice_grand_total = $invoice->total + $invoice->total_vat;
        $main = Movement::generate('invoice-payment', $user->gas, $invoice, $invoice_grand_total);
        $main->notes = __('texts.invoices.default_note', ['name' => $invoice->printableName()]);
        $movements = new Collection();
        $movements->push($main);

        [$orders_total_taxable, $orders_total_tax] = $invoice->totals();
        $alternative_types = $this->validMovementTypes();

        return view('invoice.movements', [
            'invoice' => $invoice,
            'total_orders' => $orders_total_taxable,
            'tax_orders' => $orders_total_tax,
            'movements' => $movements,
            'alternative_types' => $alternative_types,
        ]);
    }

    public function postMovements(Request $request, $id)
    {
        return $this->easyExecute(function () use ($id, $request) {
            $this->service->saveMovements($id, $request->all());

            return $this->successResponse();
        });
    }

    public function wiring(Request $request, $step, $id)
    {
        return $this->easyExecute(function () use ($id, $step, $request) {
            $this->service->wire($id, $step, $request->all());

            return $this->successResponse();
        });
    }

    private function outputCSV($elements)
    {
        $filename = sprintf('%s %s.csv', __('texts.movements.invoices'), date('d/m/Y'));
        $headers = [
            __('texts.orders.supplier'),
            __('texts.generic.date'),
            __('texts.generic.number'),
            __('texts.generic.taxable_amount'),
            __('texts.generic.vat')
        ];

        return output_csv($filename, $headers, $elements, function ($invoice) {
            return [
                $invoice->supplier->printableName(),
                $invoice->date,
                $invoice->number,
                $invoice->total,
                $invoice->total_vat,
            ];
        });
    }

    public function search(Request $request)
    {
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $supplier_id = $request->input('supplier_id');
        $elements = $this->service->list($start, $end, $supplier_id);

        $format = $request->input('format', 'none');

        if ($format == 'none') {
            $list_identifier = $request->input('list_identifier', 'invoice-list');

            return view('commons.loadablelist', [
                'identifier' => $list_identifier,
                'items' => $elements,
                'legend' => (object) [
                    'class' => Invoice::class,
                ],
            ]);
        }
        elseif ($format == 'csv') {
            return $this->outputCSV($elements);
        }
    }
}
