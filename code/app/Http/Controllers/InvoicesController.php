<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;

use App\Invoice;
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
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->number = $request->input('number');
        $invoice->date = decodeDate($request->input('date'));
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
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->number = $request->input('number');
        $invoice->date = decodeDate($request->input('date'));
        $invoice->total = $request->input('total');
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

            case 'movements':
                $invoice_grand_total = $invoice->total + $invoice->total_tax;

                $main = Movement::generate('invoice-payment', $user->gas, $invoice, $invoice_grand_total);
                $main->notes = _i('Pagamento fattura %s', $invoice->printableName());
                $movements = [$main];

                $total_orders = 0;
                $orders = Order::whereIn('id', $request->input('order_id', []))->get();
                foreach($orders as $order) {
                    $summary = $order->calculateInvoicingSummary();
                    $total_orders += $summary->total + $summary->transport;
                }

                if ($total_orders != $invoice_grand_total) {
                    $difference = $total_orders - $invoice_grand_total;
                    $movements[] = Movement::generate('invoice-payment', $user->gas, $user->gas, $difference);
                }

                $alternative_types = [];
                if (count($movements) != 1) {
                    $available_types = MovementType::types();
                    foreach($available_types as $at) {
                        if ($at->sender_type == 'App\Gas' && ($at->target_type == 'App\Supplier' || $at->target_type == 'App\Invoice'))
                            if ($difference > 0 || ($difference < 0 && $at->allow_negative))
                                $alternative_types[] = [
                                    'value' => $at->id,
                                    'label' => $at->name,
                                ];
                    }
                }

                return view('invoice.movements', [
                    'invoice' => $invoice,
                    'orders' => $orders,
                    'movements' => $movements,
                    'alternative_types' => $alternative_types
                ]);
                break;

            case 'save':
                DB::beginTransaction();

                $order_ids = $request->input('order_id', []);

                $invoice->orders()->sync($order_ids);
                $invoice->deleteMovements();

                $master_movement = null;
                $movement_types = $request->input('type', []);
                $movement_amounts = $request->input('amount', []);
                $movement_notes = $request->input('notes', []);

                for($i = 0; $i < count($movement_types); $i++) {
                    $type = $movement_types[$i];

                    $metadata = MovementType::types($type);
                    if ($metadata->target_type == 'App\Invoice') {
                        $target = $invoice;
                    }
                    else if ($metadata->target_type == 'App\Supplier') {
                        $target = $invoice->supplier;
                    }
                    else {
                        Log::error(_('Tipo movimento non riconosciuto durante il salvataggio della fattura'));
                        continue;
                    }

                    $amount = $movement_amounts[$i];
                    $mov = Movement::generate($type, $user->gas, $target, $amount);
                    $mov->notes = $movement_notes[$i];
                    $mov->save();

                    if ($type == 'invoice-payment')
                        $master_movement = $mov;
                }

                if ($master_movement != null) {
                    Order::whereIn('id', $order_ids)->update(['payment_id' => $master_movement->id, 'status' => 'archived']);

                    $invoice->status = 'payed';
                    $invoice->payment_id = $master_movement->id;
                    $invoice->save();
                }

                return $this->successResponse();
                break;
        }
    }
}
