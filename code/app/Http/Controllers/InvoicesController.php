<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use DB;
use Auth;
use Log;

use App\Invoice;
use App\Receipt;
use App\Order;
use App\Movement;
use App\MovementType;

class InvoicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Invoice'
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = new Invoice();
        $invoice->gas_id = $user->gas_id;
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->number = $request->input('number');
        $invoice->date = decodeDate($request->input('date'));
        $invoice->notes = $request->input('notes');
        $invoice->total = $request->input('total');
        $invoice->total_vat = $request->input('total_vat');
        $invoice->save();

        return $this->successResponse([
            'id' => $invoice->id,
            'name' => $invoice->name,
            'header' => $invoice->printableHeader(),
            'url' => url('invoices/' . $invoice->id),
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas)) {
            return view('invoice.edit', ['invoice' => $invoice]);
        }
        else if ($user->can('movements.view', $user->gas)) {
            return view('invoice.show', ['invoice' => $invoice]);
        }
        else {
            abort(503);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = Invoice::findOrFail($id);
        if ($request->has('supplier_id'))
            $invoice->supplier_id = $request->input('supplier_id');
        if ($request->has('number'))
            $invoice->number = $request->input('number');
        if ($request->has('date'))
            $invoice->date = decodeDate($request->input('date'));
        if ($request->has('notes'))
            $invoice->notes = $request->input('notes');
        if ($request->has('total'))
            $invoice->total = $request->input('total');
        if ($request->has('total_vat'))
            $invoice->total_vat = $request->input('total_vat');

        $invoice->status = $request->input('status');
        $invoice->save();

        return $this->successResponse([
            'id' => $invoice->id,
            'header' => $invoice->printableHeader(),
            'url' => url('invoices/' . $invoice->id),
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = Invoice::findOrFail($id);

        if ($invoice->payment != null)
            $invoice->deleteMovements();
        $invoice->delete();

        return $this->successResponse();
    }

    public function products($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = Invoice::findOrFail($id);
        $summaries = [];

        $global_summary = (object)[
            'products' => [],
            'total' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
        ];

        foreach($invoice->orders as $order) {
            $summary = $order->calculateInvoicingSummary();
            $summaries[$order->id] = $summary;

            foreach($order->products as $product) {
                if (isset($global_summary->products[$product->id]) == false) {
                    $global_summary->products[$product->id] = [
                        'name' => $product->printableName(),
                        'vat_rate' => $product->vat_rate ? $product->vat_rate->printableName() : '',
                        'total' => 0,
                        'total_vat' => 0,
                        'delivered' => 0,
                        'measure' => $product->measure
                    ];
                }

                $global_summary->products[$product->id]['total'] += $summary->products[$product->id]['total'];
                $global_summary->products[$product->id]['total_vat'] += $summary->products[$product->id]['total_vat'];
                $global_summary->products[$product->id]['delivered'] += $summary->products[$product->id]['delivered'];
            }

            $global_summary->total += $summary->total;
            $global_summary->total_taxable += $summary->total_taxable;
            $global_summary->total_tax += $summary->total_tax;
        }

        return view('invoice.products', [
            'invoice' => $invoice,
            'summaries' => $summaries,
            'global_summary' => $global_summary
        ]);
    }

    public function getMovements($id)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = Invoice::findOrFail($id);

        $invoice_grand_total = $invoice->total + $invoice->total_vat;
        $main = Movement::generate('invoice-payment', $user->gas, $invoice, $invoice_grand_total);
        $main->notes = _i('Pagamento fattura %s', $invoice->printableName());
        $movements = new Collection();
        $movements->push($main);

        $orders_total_taxable = 0;
        $orders_total_tax = 0;
        foreach($invoice->orders as $order) {
            $summary = $order->calculateInvoicingSummary();
            $orders_total_taxable += $summary->total_taxable;
            $orders_total_tax += $summary->total_tax;
        }

        $alternative_types = [];
        $available_types = MovementType::types();
        foreach($available_types as $at) {
            if (($at->sender_type == 'App\Gas' && ($at->target_type == 'App\Supplier' || $at->target_type == 'App\Invoice')) || ($at->sender_type == 'App\Supplier' && $at->target_type == 'App\Gas')) {
                $alternative_types[] = [
                    'value' => $at->id,
                    'label' => $at->name,
                ];
            }
        }

        return view('invoice.movements', [
            'invoice' => $invoice,
            'total_orders' => $orders_total_taxable,
            'tax_orders' => $orders_total_tax,
            'movements' => $movements,
            'alternative_types' => $alternative_types
        ]);
    }

    public function postMovements(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        DB::beginTransaction();

        $invoice = Invoice::findOrFail($id);
        $invoice->deleteMovements();

        $master_movement = null;
        $other_movements = [];

        $movement_types = $request->input('type', []);
        $movement_amounts = $request->input('amount', []);
        $movement_methods = $request->input('method', []);
        $movement_notes = $request->input('notes', []);

        for($i = 0; $i < count($movement_types); $i++) {
            $type = $movement_types[$i];

            $target = null;
            $sender = null;

            $metadata = MovementType::types($type);

            if ($metadata->target_type == 'App\Invoice') {
                $target = $invoice;
            }
            else if ($metadata->target_type == 'App\Supplier') {
                $target = $invoice->supplier;
            }
            else if ($metadata->target_type == 'App\Gas') {
                $target = $user->gas;
            }
            else {
                Log::error(_('Tipo movimento non riconosciuto durante il salvataggio della fattura'));
                continue;
            }

            if ($metadata->sender_type == 'App\Supplier') {
                $sender = $invoice->supplier;
            }
            else if ($metadata->sender_type == 'App\Gas') {
                $sender = $user->gas;
            }
            else {
                Log::error(_('Tipo movimento non riconosciuto durante il salvataggio della fattura'));
                continue;
            }

            $amount = $movement_amounts[$i];
            $mov = Movement::generate($type, $sender, $target, $amount);
            $mov->notes = $movement_notes[$i];
            $mov->method = $movement_methods[$i];
            $mov->save();

            if ($type == 'invoice-payment' && $master_movement == null)
                $master_movement = $mov;
            else
                $other_movements[] = $mov->id;
        }

        if ($master_movement != null) {
            foreach($invoice->orders as $order) {
                $order->payment_id = $master_movement->id;
                $order->status = 'archived';
                $order->save();
            }

            $invoice->status = 'payed';
            $invoice->payment_id = $master_movement->id;
            $invoice->save();
        }

        $invoice->otherMovements()->sync($other_movements);

        return $this->successResponse();
    }

    public function wiring(Request $request, $step, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $invoice = Invoice::findOrFail($id);

        switch($step) {
            case 'review':
                $order_ids = $request->input('order_id', []);
                $invoice->orders()->sync($order_ids);
                $invoice->status = 'to_verify';
                $invoice->save();
                return $this->successResponse();
                break;
        }
    }

    public function search(Request $request)
    {
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $supplier_id = $request->input('supplier_id');

        $query = Invoice::where(function($query) use($start, $end) {
            $query->whereHas('payment', function($query) use($start, $end) {
                $query->where('date', '>=', $start)->where('date', '<=', $end);
            })->orWhereDoesntHave('payment');
        });

        if ($supplier_id != '0')
            $query->where('supplier_id', $supplier_id);

        $elements = $query->get();

        $gas = Auth::user()->gas;
        if ($gas->hasFeature('extra_invoicing')) {
            $query = Receipt::where('date', '>=', $start)->where('date', '<=', $end)->orderBy('date', 'desc');

            if ($supplier_id != '0') {
                $query->whereHas('bookings', function($query) use ($supplier_id) {
                    $query->whereHas('order', function($query) use ($supplier_id) {
                        $query->where('supplier_id', $supplier_id);
                    });
                });
            }

            $receipts = $query->get();

            foreach($receipts as $r)
                $elements->push($r);
        }

        $elements = Invoice::doSort($elements);

        $format = $request->input('format', 'none');

        if ($format == 'none') {
            $list_identifier = $request->input('list_identifier', 'invoice-list');
            return view('commons.loadablelist', [
                'identifier' => $list_identifier,
                'items' => $elements,
                'legend' => (object)[
                    'class' => $gas->hasFeature('extra_invoicing') ? ['Invoice', 'Receipt'] : 'Invoice'
                ],
            ]);
        }
        else if ($format == 'csv') {
            $filename = _i('Esportazione fatture GAS %s.csv', date('d/m/Y'));
            $headers = [_i('Tipo'), _i('Da/A'), _i('Data'), _i('Numero'), _i('Imponibile'), _i('IVA')];
            return output_csv($filename, $headers, $elements, function($invoice) {
                $row = [];

                if (get_class($invoice) == 'App\Invoice') {
                    $row[] = _i('Ricevuta');
                    $row[] = $invoice->supplier->printableName();
                }
                else {
                    $row[] = _i('Inviata');
                    $row[] = $invoice->user->printableName();
                }

                $row[] = $invoice->date;
                $row[] = $invoice->number;
                $row[] = $invoice->total;
                $row[] = $invoice->total_vat;
                return $row;
            });
        }
    }
}
