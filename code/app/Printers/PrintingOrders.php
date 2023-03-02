<?php

namespace App\Printers;

trait PrintingOrders
{
	protected function formatTableHead($user_columns, $orders)
	{
		$all_products = [];
		$headers = $user_columns;
		$prices_rows = array_fill(0, count($user_columns), '');

		foreach ($orders as $order) {
			foreach ($order->products as $product) {
				if ($product->variants->isEmpty()) {
					$all_products[$product->id] = 0;
					$headers[] = $product->printableName();
					$prices_rows[] = printablePrice($product->price, ',');
				}
				else {
					foreach($product->variant_combos as $combo) {
						$all_products[$product->id . '-' . $combo->id] = 0;
						$headers[] = $combo->printableName();
						$prices_rows[] = printablePrice($combo->price, ',');
					}
				}
			}
		}

		$headers[] = _i('Totale Prezzo');
		$prices_rows[] = '';

		return [$all_products, $headers, $prices_rows];
	}

	protected function bookingsRules($status)
	{
		if ($status == 'saved' || $status == 'delivered') {
            $get_total = 'delivered';
            $get_function = 'getDeliveredQuantity';
            $get_function_real = false;
        }
        else {
            $get_total = 'booked';
            $get_function = 'getBookedQuantity';
            $get_function_real = true;
        }

		return [$get_total, $get_function, $get_function_real];
	}

	protected function formatBookingInTable($order, $booking, $status, &$all_products)
	{
		$row = [];
		list($get_total, $get_function, $get_function_real) = $this->bookingsRules($status);

		foreach ($order->products as $product) {
			if ($product->variants->isEmpty()) {
				if ($booking) {
					$quantity = $booking->$get_function($product, $get_function_real, false);
				}
				else {
					$quantity = 0;
				}

				$all_products[$product->id] += $quantity;
				$row[] = printableQuantity($quantity, $product->measure->discrete, 3, ',');
			}
			else {
				foreach($product->variant_combos as $combo) {
					if ($booking) {
						$quantity = $booking->$get_function($combo, $get_function_real, false);
					}
					else {
						$quantity = 0;
					}

					$all_products[$product->id . '-' . $combo->id] += $quantity;
					$row[] = printableQuantity($quantity, $product->measure->discrete, 3, ',');
				}
			}
		}

		return $row;
	}

	protected function formatTableFooter($orders, $user_columns, $all_products, $total_price)
	{
		$row = [];

		$row[] = _i('Totale');
		$row = array_merge($row, array_fill(0, count($user_columns) - 1, ''));

		foreach ($orders as $order) {
			foreach ($order->products as $product) {
				if ($product->variants->isEmpty()) {
					$row[] = printableQuantity($all_products[$product->id], $product->measure->discrete, 3, ',');
				}
				else {
					foreach($product->variant_combos as $combo) {
						$row[] = printableQuantity($all_products[$product->id . '-' . $combo->id], $product->measure->discrete, 3, ',');
					}
				}
			}
		}

		$row[] = printablePrice($total_price);
		return $row;
	}

	/*
		TODO Sarebbe opportuno astrarre il tipo di azione desiderata:
		- save per il salvataggio del file e la restituzione del path
		- mail per inviare la mail (al posto del flag send_mail)
		- output per mandare direttamente in output e far scaricare il file
	*/
	public function document($obj, $type, $request)
	{
		switch ($type) {
			/*
				Dettaglio Consegne
			*/
			case 'shipping':
				return $this->handleShipping($obj, $request);

			/*
				Riassunto Prodotti
			*/
			case 'summary':
				return $this->handleSummary($obj, $request);

			/*
				Tabella Complessiva
			*/
			case 'table':
				return $this->handleTable($obj, $request);

			default:
				\Log::error('Unrecognized type for Aggregate/Order document: ' . $type);
				return null;
		}
	}

	protected abstract function handleShipping($obj, $request);
	protected abstract function handleSummary($obj, $request);
	protected abstract function handleTable($obj, $request);
}
