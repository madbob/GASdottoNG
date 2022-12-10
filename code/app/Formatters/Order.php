<?php

/*
	Attenzione: questo formatter è un po' anomalo, va usato diversamente
	rispetto a tutti gli altri.
	TODO: uniformare l'API
*/

namespace App\Formatters;

class Order extends Formatter
{
	public static function formattableColumns($type = null)
    {
		$ret = [
			'name' => (object) [
				'name' => _i('Nome Prodotto'),
				'checked' => true,
				'format_product' => function($product, $summary) {
					return $product->printableName();
				},
				'format_variant' => function($product, $summary) {
					return $product->printableName() . ' - ' . $summary->variant->printableName();
				}
			],
			'supplier' => (object) [
				'name' => _i('Fornitore'),
				'checked' => false,
				'format_product' => function($product, $summary) {
					return $product->supplier->printableName();
				},
			],
			'code' => (object) [
				'name' => _i('Codice Fornitore'),
				'format_product' => function($product, $summary) {
					return $product->supplier_code;
				},
				'format_variant' => function($product, $summary) {
					if (!empty($summary->variant->supplier_code)) {
						return $summary->variant->supplier_code;
					}
					else {
						return $summary->variant->product->product->supplier_code;
					}
				}
			],
			'quantity' => (object) [
				'name' => _i('Quantità'),
				'checked' => true,
				'format_product' => function($product, $summary, $alternate = false) {
					if ($alternate == false)
						return printableQuantity($summary->quantity_pieces, $product->measure->discrete, 2, ',');
					else
						return printableQuantity($summary->delivered_pieces, $product->measure->discrete, 2, ',');
				},
			],
			'boxes' => (object) [
				'name' => _i('Numero Confezioni'),
				'format_product' => function($product, $summary, $alternate = false) {
					if ($product->package_size != 0) {
						if ($alternate == false)
							return $summary->quantity_pieces / $product->package_size;
						else
							return $summary->delivered_pieces / $product->package_size;
					}
					else {
						return '';
					}
				},
			],
			'measure' => (object) [
				'name' => _i('Unità di Misura'),
				'checked' => true,
				'format_product' => function($product, $summary, $alternate = false) {
					if ($alternate == false) {
						return $product->printableMeasure(true);
					}
					else {
						if ($product->portion_quantity != 0) {
							return $product->measure->name;
						}
						else {
							return $product->printableMeasure(true);
						}
					}
				},
			],
			'category' => (object) [
				'name' => _i('Categoria'),
				'checked' => false,
				'format_product' => function($product, $summary) {
					return $product->category ? $product->category->name : '';
				},
			],
			'unit_price' => (object) [
				'name' => _i('Prezzo Unitario'),
				'checked' => false,
				'format_product' => function($product, $summary) {
					return printablePrice($product->price, ',');
				},
				'format_variant' => function($product, $summary) {
					return printablePrice($summary->variant->unitPrice(), ',');
				}
			],
			'price' => (object) [
				'name' => _i('Prezzo'),
				'checked' => true,
				'format_product' => function($product, $summary, $alternate = false) {
					if ($alternate == false)
						return printablePrice($summary->price, ',');
					else
						return printablePrice($summary->price_delivered, ',');
				},
			],
		];

		if ($type == 'summary') {
			$ret['notes'] = (object) [
				'name' => _i('Note Prodotto'),
				'format_product' => function($product, $summary) {
					return $product->pivot->notes;
				},
			];
		}

		return $ret;
	}
}
