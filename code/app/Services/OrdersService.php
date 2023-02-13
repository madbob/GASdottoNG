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
        $suppliers = Arr::wrap($request['supplier_id']);

        if (count($suppliers) > 1) {
            $a->comment = $request['comment'] ?? '';
            $request['comment'] = '';
        }

        $a->save();

        $deliveries = array_filter($request['deliveries'] ?? []);
        $request['keep_open_packages'] = $request['keep_open_packages'] ?? 'no';

        foreach($suppliers as $index => $supplier_id) {
            $supplier = Supplier::findOrFail($supplier_id);
            $this->ensureAuth(['supplier.orders' => $supplier]);

            $o = new Order();
            $o->supplier_id = $supplier->id;

            $this->setCommonAttributes($o, $request);
            $o->status = $request['status'];
            $o->aggregate_id = $a->id;
            $o->aggregate_sorting = $index;
            $o->save();

            $o->deliveries()->sync($deliveries);
        }

        return $a;
    }

    public function update($id, array $request)
    {
        DB::beginTransaction();

        $order = $this->show($id, true);
        $this->setCommonAttributes($order, $request);
        $order->deliveries()->sync(array_filter($request['deliveries'] ?? []));
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

        /*
            Se vengono rimossi dei prodotti dall'ordine, ne elimino tutte le
            relative prenotazioni sinora avvenute
        */
        $enabled = $request['enabled'] ?? [];
        $removed_products = $order->products()->whereNotIn('id', $enabled)->pluck('id')->toArray();
        if (!empty($removed_products)) {
            foreach($order->bookings as $booking) {
                $booking->products()->whereIn('product_id', $removed_products)->delete();

				/*
					Se i prodotti rimossi erano gli unici contemplati nella
					prenotazione, elimino tutta la prenotazione
				*/
                if ($booking->products()->count() == 0) {
                    $booking->delete();
                }
            }
        }

        $order->products()->sync($enabled);
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
        switch($action) {
            case 'none':
                break;

            case 'adjust':
                $order = $this->show($id, true);
                $aggregate = $order->aggregate;
                $hub = App::make('GlobalScopeHub');
                $initial_gas = $hub->getGas();

                foreach($aggregate->gas as $gas) {
                    $hub->setGas($gas->id);
                    $redux = $aggregate->reduxData();

                    foreach($aggregate->orders as $order) {
                        foreach($order->bookings as $booking) {
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
