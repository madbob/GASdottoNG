<?php

namespace App\Services;

use Illuminate\Support\Arr;

use App;
use DB;

use App\Order;
use App\Aggregate;
use App\Supplier;

class OrdersService extends BaseService
{
    public function show($id, $edit = false)
    {
        $ret = Order::findOrFail($id);

        if ($edit) {
            $this->ensureAuth(['supplier.orders' => $ret->supplier]);
        }

        return $ret;
    }

    private function setCommonAttributes($order, $request)
    {
        $this->setIfSet($order, $request, 'comment');
        $this->transformAndSetIfSet($order, $request, 'start', 'decodeDate');
        $this->transformAndSetIfSet($order, $request, 'end', 'decodeDate');
        $this->transformAndSetIfSet($order, $request, 'shipping', 'decodeDate');
        $this->setIfSet($order, $request, 'keep_open_packages');

        return $order;
    }

    public function store(array $request)
    {
        DB::beginTransaction();

        $a = new Aggregate();
        $suppliers = Arr::wrap($request['supplier']);

        if (count($suppliers) > 1) {
            $a->comment = $request['comment'] ?? '';
            $request['comment'] = '';
        }

        $a->save();

        $circles = array_filter($request['circles'] ?? []);
        $request['keep_open_packages'] = $request['keep_open_packages'] ?? 'no';

        foreach ($suppliers as $index => $supplier_id) {
            $supplier = Supplier::findOrFail($supplier_id);
            $this->ensureAuth(['supplier.orders' => $supplier]);

            $o = new Order();
            $o->supplier_id = $supplier->id;

            $this->setCommonAttributes($o, $request);
            $o->status = $request['status'];
            $o->aggregate_id = $a->id;
            $o->aggregate_sorting = $index;
            $o->save();

            $o->circles()->sync($circles);
        }

        return $a;
    }

    public function update($id, array $request)
    {
        DB::beginTransaction();

        $order = $this->show($id, true);
        $this->setCommonAttributes($order, $request);
        $order->circles()->sync($request['circles'] ?? []);
        $order->users()->sync($request['users'] ?? []);

        /*
            Se un ordine viene riaperto, modifico artificiosamente la sua data
            di chiusura. Questo per evitare che venga nuovamente automaticamente
            chiuso
        */
        $status = $request['status'] ?? $order->status;
        if ($order->status != $status) {
            $today = date('Y-m-d');
            if ($status == 'open' && $order->end < $today) {
                $order->end = $today;
            }

            $order->status = $status;
        }

        $order->save();

        $enabled = $request['enabled'] ?? [];

        $removed_products = $order->products()->whereNotIn('id', $enabled)->get();
        foreach ($removed_products as $rp) {
            $order->detachProduct($rp);
        }

        /*
            Nota bene: mentre l'ordine è aperto, alcuni prodotti potrebbero
            essere stati rimossi ma comunque lasciati nell'ordine stesso.
            Quando aggiorno l'elenco, devo tenere in considerazione l'elenco di
            tutti i prodotti del fornitore inclusi quelli marcati come eliminati
        */
        $products = $order->supplier->products()->withTrashed()->whereIn('id', $enabled)->get();
        $order->syncProducts($products, false);

        return $order->aggregate;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        $order = $this->show($id, true);
        $order->delete();

        return $order;
    }

    public function fixModifiers($id, $action)
    {
        switch ($action) {
            case 'none':
                break;

            case 'adjust':
                $order = $this->show($id, true);
                $aggregate = $order->aggregate;
                $hub = App::make('GlobalScopeHub');
                $initial_gas = $hub->getGas();

                foreach ($aggregate->gas as $gas) {
                    $hub->setGas($gas->id);
                    $redux = $aggregate->reduxData();

                    foreach ($aggregate->orders as $order) {
                        foreach ($order->bookings as $booking) {
                            $booking->saveModifiers($redux);
                            $booking->fixPayment();
                        }
                    }
                }

                $hub->setGas($initial_gas);
                break;
        }

        return true;
    }
}
