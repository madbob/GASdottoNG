<?php

namespace App\Services;

use App\Exceptions\AuthException;

use Auth;
use Log;
use DB;
use PDF;

use App\Invoice;
use App\Receipt;
use App\Order;
use App\Movement;
use App\MovementType;

class InvoicesService extends BaseService
{
    public function list($start, $end, $supplier_id)
    {
        $supplier = Supplier::tFind($supplier_id);
        $user = $this->ensureAuth(['supplier.invoices' => $supplier, 'supplier.movements' => $supplier]);

        $query = Invoice::where(function($query) use($start, $end) {
            $query->whereHas('payment', function($query) use($start, $end) {
                $query->where('date', '>=', $start)->where('date', '<=', $end);
            })->orWhereDoesntHave('payment');
        });

        if ($supplier) {
            $query->where('supplier_id', $supplier->id);
        }
        else {
            $suppliers = array_merge($user->targetsByAction('supplier.orders'), $user->targetsByAction('supplier.movements'));
            $query->whereIn('supplier_id', array_keys($suppliers));
        }

        $elements = $query->get();
        return Invoice::doSort($elements);
    }

    public function show($id)
    {
        $ret = Invoice::findOrFail($id);
        $this->ensureAuth(['supplier.invoices' => $ret->supplier, 'supplier.movements' => $ret->supplier]);
        return $ret;
    }

    private function setCommonAttributes($invoice, $request)
    {
        $this->setIfSet($invoice, $request, 'supplier_id');
        $this->setIfSet($invoice, $request, 'number');
        $this->transformAndSetIfSet($invoice, $request, 'date', "decodeDate");
        $this->setIfSet($invoice, $request, 'notes');
        $this->transformAndSetIfSet($invoice, $request, 'total', 'enforceNumber');
        $this->transformAndSetIfSet($invoice, $request, 'total_vat', 'enforceNumber');

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
        return $this->setCommonAttributes($invoice, $request);
    }

    public function update($id, array $request)
    {
        $invoice = Invoice::findOrFail($id);
        $user = $this->ensureAuth(['supplier.invoices' => $invoice->supplier]);
        $invoice->gas_id = $user->gas_id;
        return $this->setCommonAttributes($invoice, $request);
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
