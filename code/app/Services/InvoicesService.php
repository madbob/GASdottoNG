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
        $user = $this->ensureAuth(['movements.admin' => 'gas']);

        $query = Invoice::where(function($query) use($start, $end) {
            $query->whereHas('payment', function($query) use($start, $end) {
                $query->where('date', '>=', $start)->where('date', '<=', $end);
            })->orWhereDoesntHave('payment');
        });

        if ($supplier_id != '0') {
            $query->where('supplier_id', $supplier_id);
        }

        $elements = $query->get();
        return Invoice::doSort($elements);
    }

    public function show($id)
    {
        return Invoice::findOrFail($id);
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
        return $invoice;
    }

    public function store(array $request)
    {
        $user = $this->ensureAuth(['movements.admin' => 'gas']);

        $invoice = new Invoice();
        $invoice->gas_id = $user->gas_id;
        return $this->setCommonAttributes($invoice, $request);
    }

    public function update($id, array $request)
    {
        $user = $this->ensureAuth(['movements.admin' => 'gas']);

        $invoice = Invoice::findOrFail($id);
        $invoice->gas_id = $user->gas_id;
        return $this->setCommonAttributes($invoice, $request);
    }

    public function products($id)
    {
        $user = $this->ensureAuth(['movements.admin' => 'gas']);
        $invoice = $this->show($id);
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

        return [
            'invoice' => $invoice,
            'summaries' => $summaries,
            'global_summary' => $global_summary
        ];
    }

	public function wire($id, $step, $request)
	{
		$this->ensureAuth(['movements.admin' => 'gas']);
		$invoice = $this->show($id);

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
        $user = $this->ensureAuth(['movements.admin' => 'gas']);
        $invoice = $this->show($id);

        $invoice->deleteMovements();

        if ($invoice->payment != null) {
            $invoice->payment->delete();
        }

        $invoice->delete();
        return $invoice;
    }
}
