<?php

function easyFilterOrders($supplier, $startdate, $enddate, $statuses = null)
{
	if (is_object($supplier))
		$supplier_id = $supplier->id;
	else
		$supplier_id = $supplier;

	if ($statuses == null) {
		$statuses = array_keys(App\Helpers\Status::orders());
	}

	/*
		Questa funzione dovrebbe prendere in considerazione anche i permessi
		dell'utente corrente, e tornare solo gli aggregati che contengono
		ordini tipo:
		$user->can('supplier.orders', $order->supplier) || $user->can('supplier.shippings', $order->supplier)
	*/

	$orders = App\Aggregate::with('orders')->whereHas('orders', function ($query) use ($supplier_id, $startdate, $enddate, $statuses) {
		if (!empty($supplier_id)) {
			if (is_array($supplier_id))
				$query->whereIn('supplier_id', $supplier_id);
			else
				$query->where('supplier_id', $supplier_id);
		}

		if (!empty($startdate))
			$query->where('start', '>=', $startdate);

		if (!empty($enddate))
			$query->where('end', '<=', $enddate);

		$query->whereIn('status', $statuses);
	})->get();

	$orders->sort(function($a, $b) {
		return strcmp($a->shipping, $b->shipping);
	});

	return $orders;
}

function defaultOrders($mine)
{
	if ($mine) {
		$user = Auth::user();
		$supplier_id = [];

		foreach($user->targetsByAction('supplier.modify') as $supplier) {
			$supplier_id[] = $supplier->id;
		}
		foreach($user->targetsByAction('supplier.orders') as $supplier) {
			$supplier_id[] = $supplier->id;
		}
		foreach($user->targetsByAction('supplier.shippings') as $supplier) {
			$supplier_id[] = $supplier->id;
		}

		$supplier_id = array_unique($supplier_id);
	}
	else {
		$supplier_id = 0;
	}

	$valid_statuses = [];
	foreach(App\Helpers\Status::orders() as $identifier => $meta) {
		if ($meta->default_display) {
			$valid_statuses[] = $identifier;
		}
	}

	return easyFilterOrders($supplier_id, date('Y-m-d', strtotime('-1 years')), date('Y-m-d', strtotime('+1 years')), $valid_statuses);
}

function getOrdersByStatus($user, $status)
{
	$eager_load = ['orders', 'orders.products', 'orders.bookings', 'orders.bookings.modifiedValues', 'orders.modifiers'];

	switch($status) {
		/*
			Se cerco gli ordini aperti ed è stata abilitata la funzione per
			gestire gli ordini incompleti, devo considerare anche quelli chiusi
			ma con confezioni da completare
		*/
		case 'open':
			return $aggregates = App\Aggregate::whereHas('orders', function ($query) {
				$query->whereIn('status', ['open', 'closed'])->accessibleBooking();
			})->with($eager_load)->get()->filter(function($a) {
				return $a->status == 'open' || $a->hasPendingPackages();
			});

		case 'closed':
			return App\Aggregate::whereHas('orders', function ($query) use ($user) {
				$query->whereIn('status', ['closed', 'user_payment'])->where(function($query) use ($user) {
					$query->whereHas('bookings', function($query) use ($user) {
						$query->where('status', '!=', 'shipped')->where(function($query) use ($user) {
							$query->where('user_id', $user->id)->orWhereIn('user_id', $user->friends->pluck('id'));
						});
					})->orWhere(function($query) {
						$query->accessibleBooking();
					})->orWhere(function($query) use ($user) {
						$supplier_shippings = array_keys($user->targetsByAction('supplier.shippings'));
						$query->whereIn('supplier_id', $supplier_shippings);
					});
				});
			})->with($eager_load)->get();
	}
}
