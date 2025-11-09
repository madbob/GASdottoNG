<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Order;
use App\Aggregate;

class AggregatesService extends BaseService
{
    public function store(array $request)
    {
        DB::beginTransaction();

        $data = $request['data'];
        $data = json_decode($data);

        foreach ($data as $a) {
            if ($a->id == 'new') {
                $aggr = new Aggregate();
                $aggr->save();
            }
            else {
                $aggr = Aggregate::find($a->id);
                if (is_null($aggr)) {
                    continue;
                }
            }

            $circles = $aggr->circles->pluck('id');
            $old_aggregates = [];

            foreach ($a->orders as $index => $o) {
                $order = Order::find($o);
                if ($order) {
                    $old_aggregates[] = $order->aggregate;
                    $order->aggregate_id = $aggr->id;
                    $order->aggregate_sorting = $index;
                    $order->save();
                    $order->circles()->sync($circles);
                }
            }

            $aggr->transferAttachmentsFrom($old_aggregates);
        }

        foreach (Aggregate::doesnthave('orders')->get() as $ea) {
            $ea->delete();
        }

        return null;
    }

    public function update($id, array $request)
    {
        DB::beginTransaction();

        $a = Aggregate::findOrFail($id);
        $this->setIfSet($a, $request, 'comment');
        $a->save();

        $status = $request['status'] ?? 'no';
        if ($status != 'no') {
            $a->orders()->update(['status' => $status]);
        }

        $circles = array_filter($request['circles'] ?? []);
        foreach ($a->orders as $o) {
            $o->circles()->sync($circles);
        }

        if (isset($request['change_dates'])) {
            $a->orders()->update([
                'start' => decodeDate($request['start']),
                'end' => decodeDate($request['end']),
                'shipping' => decodeDate($request['shipping']),
            ]);
        }

        return $a;
    }
}
