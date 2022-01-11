<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use App\Services\InvoicesService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use DB;
use Auth;
use Log;

use App\Invoice;
use App\Order;
use App\Movement;
use App\MovementType;

class InvoicesController extends BackedController
{
    public function __construct(InvoicesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Invoice',
            'endpoint' => 'invoices',
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

    public function show($id)
    {
        $invoice = $this->service->show($id);

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

    public function products($id)
    {
        return view('invoice.products', $this->service->products($id));
    }

    public function orders($id)
    {
        $invoice = Invoice::findOrFail($id);

        return view('invoice.orders', [
            'invoice' => $invoice,
        ]);
    }

    private function validMovementTypes()
    {
        $alternative_types = [];
        $available_types = movementTypes();

        foreach($available_types as $at) {
            if (($at->sender_type == 'App\Gas' && ($at->target_type == 'App\Supplier' || $at->target_type == 'App\Invoice')) || ($at->sender_type == 'App\Supplier' && $at->target_type == 'App\Gas')) {
                $alternative_types[$at->id] = $at->name;
            }
        }

        return $alternative_types;
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

        list($orders_total_taxable, $orders_total_tax) = $invoice->totals();
        $alternative_types = $this->validMovementTypes();

        return view('invoice.movements', [
            'invoice' => $invoice,
            'total_orders' => $orders_total_taxable,
            'tax_orders' => $orders_total_tax,
            'movements' => $movements,
            'alternative_types' => $alternative_types
        ]);
    }

    private function movementAttach($type, $user, $invoice)
    {
        $target = null;
        $sender = null;

        $metadata = movementTypes($type);

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
        }

        if ($metadata->sender_type == 'App\Supplier') {
            $sender = $invoice->supplier;
        }
        else if ($metadata->sender_type == 'App\Gas') {
            $sender = $user->gas;
        }
        else {
            Log::error(_('Tipo movimento non riconosciuto durante il salvataggio della fattura'));
        }

        return [$sender, $target];
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

            list($sender, $target) = $this->movementAttach($type, $user, $invoice);
            if (is_null($sender) || is_null($target)) {
                continue;
            }

            $amount = $movement_amounts[$i];
            $mov = Movement::generate($type, $sender, $target, $amount);
            $mov->notes = $movement_notes[$i];
            $mov->method = $movement_methods[$i];
            $mov->save();

            if ($type == 'invoice-payment' && $master_movement == null) {
                $master_movement = $mov;
            }
            else {
                $other_movements[] = $mov->id;
            }
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
        }
    }

    private function outputCSV($elements)
    {
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

    public function search(Request $request)
    {
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $supplier_id = $request->input('supplier_id');
        $elements = $this->service->list($start, $end, $supplier_id);
        $gas = $request->user()->gas;

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
            return $this->outputCSV($elements);
        }
    }
}
