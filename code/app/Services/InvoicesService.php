<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Gas;
use App\Supplier;
use App\Invoice;
use App\Receipt;
use App\Order;
use App\Movement;
use App\MovementType;

class InvoicesService extends BaseService
{
    private function testAccess($supplier)
    {
        return $this->ensureAuth(['movements.admin' => 'gas', 'supplier.invoices' => $supplier, 'supplier.movements' => $supplier]);
    }

    public function list($start, $end, $supplier_id)
    {
        if ($supplier_id) {
            $supplier = Supplier::tFind($supplier_id);
        }
        else {
            $supplier = null;
        }

        $user = $this->testAccess($supplier);

        $query = Invoice::where(function($query) use($start, $end) {
            $query->whereHas('payment', function($query) use($start, $end) {
                $query->where('date', '>=', $start)->where('date', '<=', $end);
            })->orWhereDoesntHave('payment');
        });

        if ($supplier) {
            $query->where('supplier_id', $supplier->id);
        }
        else {
            $suppliers = $user->targetsByAction('movements.admin,supplier.orders,supplier.movements');
            $query->whereIn('supplier_id', array_keys($suppliers));
        }

        $elements = $query->get();
        return Invoice::doSort($elements);
    }

    public function show($id)
    {
        $ret = Invoice::findOrFail($id);
        $user = $this->testAccess($ret->supplier);
        return $ret;
    }

    private function setCommonAttributes($invoice, $request, $user)
    {
        if (isset($request['supplier_id'])) {
            $supplier = Supplier::tFind($request['supplier_id']);
        }
        else {
            $supplier = $invoice->supplier;
        }

        if ($user->can('supplier.invoices', $supplier)) {
            $invoice->supplier_id = $supplier->id;
            $this->setIfSet($invoice, $request, 'number');
            $this->transformAndSetIfSet($invoice, $request, 'date', "decodeDate");
            $this->transformAndSetIfSet($invoice, $request, 'total', 'enforceNumber');
            $this->transformAndSetIfSet($invoice, $request, 'total_vat', 'enforceNumber');
        }

        $this->setIfSet($invoice, $request, 'notes');

        if (isset($request['status'])) {
            $this->setIfSet($invoice, $request, 'status');
        }

        $invoice->save();
        $invoice->attachByRequest($request);
        return $invoice;
    }

    public function store(array $request)
    {
        $supplier = Supplier::tFind($request['supplier_id']);
        if ($supplier) {
            $user = $this->ensureAuth(['supplier.invoices' => $supplier]);
        }
        else {
            throw new IllegalArgumentException(_i('Fornitore non specificato'), 1);
        }

        $invoice = new Invoice();
        $invoice->gas_id = $user->gas_id;
        return $this->setCommonAttributes($invoice, $request, $user);
    }

    public function update($id, array $request)
    {
        $invoice = Invoice::findOrFail($id);
        $user = $this->testAccess($invoice->supplier);
        $invoice->gas_id = $user->gas_id;
        return $this->setCommonAttributes($invoice, $request, $user);
    }

    private function initGlobalSummeries($invoice)
    {
        $global_summary = (object)[
            'products' => [],
            'total' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
        ];

        foreach($invoice->orders as $order) {
            foreach($order->products as $product) {
                $global_summary->products[$product->id] = [
                    'name' => $product->printableName(),
                    'vat_rate' => $product->vat_rate ? $product->vat_rate->printableName() : '',
                    'total' => 0,
                    'total_vat' => 0,
                    'delivered' => 0,
                    'measure' => $product->measure
                ];
            }
        }

        return $global_summary;
    }

    public function products($id)
    {
        $invoice = $this->show($id);

        $summaries = [];
        $global_summary = $this->initGlobalSummeries($invoice);

        foreach($invoice->orders as $order) {
            $summary = $order->calculateInvoicingSummary();
            $summaries[$order->id] = $summary;

            foreach($order->products as $product) {
                $global_summary->products[$product->id]['total'] += $summary->products[$product->id]['total'];
                $global_summary->products[$product->id]['total_vat'] += $summary->products[$product->id]['total_vat'];
                $global_summary->products[$product->id]['delivered'] += $summary->products[$product->id]['delivered'];
            }

            $global_summary->total += $summary->total;
            $global_summary->total_taxable += $summary->total_taxable;
            $global_summary->total_tax += $summary->total_tax;
        }

        return [
            'invoice' => $invoice,
            'summaries' => $summaries,
            'global_summary' => $global_summary
        ];
    }

	public function wire($id, $step, $request)
	{
		$invoice = $this->show($id);
        $this->ensureAuth(['supplier.invoices' => $invoice->supplier]);

		switch($step) {
			case 'review':
				$order_ids = $request['order_id'] ?? [];
				$invoice->orders()->sync($order_ids);
				$invoice->status = 'to_verify';
				$invoice->save();
				break;
		}
	}

    private function guessPeer($invoice, $mov_target_type, $user)
    {
        $peer = null;

        if ($mov_target_type == Invoice::class) {
            $peer = $invoice;
        }
        else if ($mov_target_type == Supplier::class) {
            $peer = $invoice->supplier;
        }
        else if ($mov_target_type == Gas::class) {
            $peer = $user->gas;
        }
        else {
            \Log::error(_('Tipo movimento non riconosciuto durante il salvataggio della fattura'));
        }

        return $peer;
    }

    private function movementAttach($type, $user, $invoice)
    {
        $metadata = movementTypes($type);
        $target = $this->guessPeer($invoice, $metadata->target_type, $user);
        $sender = $this->guessPeer($invoice, $metadata->sender_type, $user);
        return [$sender, $target];
    }

    public function saveMovements($id, $request)
    {
        $invoice = $this->show($id);
        $this->ensureAuth(['movements.admin' => 'gas', 'supplier.movements' => $invoice->supplier]);

        $invoice->deleteMovements();

        $master_movement = null;
        $other_movements = [];

        $movement_types = $request['type'] ?? [];
        $movement_amounts = $request['amount'] ?? [];
        $movement_methods = $request['method'] ?? [];
        $movement_notes = $request['notes'] ?? [];

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
    }

    public function destroy($id)
    {
        $invoice = $this->show($id);
        $this->ensureAuth(['supplier.invoices' => $invoice->supplier]);

        $invoice->deleteMovements();

        if ($invoice->payment != null) {
            $invoice->payment->delete();
        }

        $invoice->delete();
        return $invoice;
    }
}
