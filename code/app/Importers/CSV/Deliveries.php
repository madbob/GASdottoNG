<?php

namespace App\Importers\CSV;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Aggregate;
use App\Order;

class Deliveries extends CSVImporter
{
	protected function fields()
	{
		$ret = [
			'username' => (object) [
				'label' => _i('Username'),
				'mandatory' => true,
			],
			'first' => (object) [
				'label' => _i('Primo prodotto'),
				'mandatory' => true,
				'explain' => _i("Usa questo elemento per identificare il primo prodotto che appare nell'elenco"),
			],
		];

		return $ret;
	}

	public function testAccess($request)
	{
		$user = $request->user();
		$aggregate_id = $request->input('aggregate_id');
		$aggregate = Aggregate::findOrFail($aggregate_id);
		return $user->can('supplier.shippings', $aggregate);
	}

	public function guess($request)
	{
		return $this->storeUploadedFile($request, [
			'type' => 'deliveries',
			'next_step' => 'select',
			'sorting_fields' => $this->fields(),
			'extra_fields' => [
				'aggregate_id' => $request->input('aggregate_id'),
			],
			'extra_description' => [
				_i("Nota bene: come nelle normali consegne, si assume che la quantità consegnata dei prodotti con pezzatura sia qui espressa a peso e non in numero di pezzi."),
			]
		]);
	}

	public function select($request)
	{
		$user = Auth::user();
		$service = app()->make('BookingsService');
		$errors = [];

		list($reader, $columns) = $this->initRead($request);
		$target_separator = ',';

		$aggregate_id = $request->input('aggregate_id');
		$aggregate = Aggregate::findOrFail($aggregate_id);

		$mapped_products = [];
		$target_order = null;

		list($first_product_index) = $this->getColumnsIndex($columns, ['first']);
		$csvdata = iterator_to_array($reader->getRecords());

		$header = $csvdata[0];

		for ($i = $first_product_index; $i < count($header); $i++) {
			$name = $header[$i];

			foreach ($aggregate->orders as $order) {
				if ($name == _i('Totale Prezzo')) {
					continue;
				}

				$p = productByString($name, $order->products);

				if ($p) {
					if ($target_order && $target_order->id != $order->id) {
						throw new \Exception(_i('Operazione fallita: nel documento importato sono presenti prodotti di diversi ordini'), 1);
					}

					$target_order = $order;

					$mapped_products[$i] = (object) [
						'product' => $p[0],
						'combo' => $p[1],
					];

					break;
				}
				else {
					$errors[] = _i('Prodotto non identificato: %s', [$name]);
				}
			}
		}

		$bookings = [];
		$data = [];

		if (is_null($target_order)) {
			$errors[] = _i('Ordine non identificato');
		}
		else {
			DB::beginTransaction();

			for ($i = 2; $i < count($csvdata); $i++) {
				$line = $csvdata[$i];

				try {
					$datarow = [
						'action' => 'saved',
					];

					$target_user = null;
					$skip_row = false;

					foreach ($columns as $index => $field) {
						if ($field == 'username') {
							$username = trim($line[$index]);
							$target_user = User::where('username', $username)->first();
							if (is_null($target_user)) {
								$skip_row = true;
							}
						}
						elseif ($index >= $first_product_index) {
							if (isset($mapped_products[$index])) {
								$quantity = guessDecimal($line[$index]);
								$product_id = $mapped_products[$index]->product->id;
								$datarow[$product_id] = $quantity;

								if ($mapped_products[$index]->combo) {
									if (isset($datarow['variant_quantity_' . $product_id]) == false) {
										$datarow['variant_quantity_' . $product_id] = [];
									}

									foreach ($mapped_products[$index]->combo->values as $val) {
										$variant_id = $val->variant->id;

										if (isset($datarow['variant_selection_' . $variant_id]) == false) {
											$datarow['variant_selection_' . $variant_id] = [];
										}

										$datarow['variant_selection_' . $variant_id][] = $val->id;
									}

									$datarow['variant_quantity_' . $product_id][] = $quantity;
								}
							}
						}

						if ($skip_row == true) {
							break;
						}
					}

					if ($target_user) {
						$booking = $service->handleBookingUpdate($datarow, $user, $target_order, $target_user, true);

						$data[] = $datarow;
						$bookings[] = (object) [
							'user_id' => $target_user->id,
							'user_name' => $target_user->printableName(),
							'total' => $booking->getValue('effective', true),
						];
					}
				}
				catch (\Exception $e) {
					$errors[] = implode($target_separator, $line) . '<br/>' . $e->getMessage();
				}
			}

			DB::rollback();
		}

		return [
			'bookings' => $bookings,
			'aggregate_id' => $aggregate_id,
			'order_id' => $target_order ? $target_order->id : 0,
			'data' => $data,
			'errors' => $errors,
		];
	}

	public function formatSelect($parameters)
	{
		return view('import.csvbookingsselect', $parameters);
	}

	public function run($request)
	{
		$user = Auth::user();
		$service = app()->make('BookingsService');

		$data = json_decode($request->input('data', '[]'), true);
		$users = $request->input('user', []);
		$action = $request->input('action', 'save');

		$order_id = $request->input('order_id');
		$target_order = Order::findOrFail($order_id);

		$errors = [];
		$bookings = [];

		DB::beginTransaction();

		foreach($data as $index => $datarow) {
			try {
				$target_user = User::find($users[$index]);
				$booking = $service->handleBookingUpdate($datarow, $user, $target_order, $target_user, true);
				$bookings[] = $booking;
			}
			catch (\Exception $e) {
				$errors[] = $index . '<br/>' . $e->getMessage();
			}
		}

		DB::commit();

		if ($action == 'close') {
			app()->make('FastBookingsService')->fastShipping($user, $target_order->aggregate, null);
		}

		return [
			'title' => _i('Consegne importate'),
			'objects' => $bookings,
			'errors' => $errors
		];
	}

	public function finalTemplate()
    {
        return 'import.csvimportbookingsfinal';
    }
}
