<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Theme;
use DB;

use App\Aggregate;
use App\Order;

class AggregatesController extends OrdersController
{
	public function create(Request $request)
	{
		$orders = Aggregate::orderBy('id', 'desc')->get();
		return view('order.aggregable', ['orders' => $orders]);
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$data = $request->input('data');
		$data = json_decode($data);

		foreach ($data as $a) {
			if (empty($a->orders)) {
				$aggr = Aggregate::find($a->id);
				if ($aggr != null)
					$aggr->delete();
			}
			else {
				if ($a->id == 'new') {
					$aggr = new Aggregate();
					$aggr->save();
					$id = $aggr->id;
				}
				else {
					$id = $a->id;
				}

				foreach ($a->orders as $o) {
					$order = Order::find($o);
					if ($order->aggregate_id != $id) {
						$order->aggregate_id = $id;
						$order->save();
					}
				}
			}
		}

		return $this->successResponse();
	}

	public function show($id)
	{
		$a = Aggregate::findOrFail($id);
		return Theme::view('order.aggregate', ['aggregate' => $a]);
	}
}
