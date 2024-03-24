<?php

namespace App\Printers\Concerns;

trait Orders
{
    use Summary, Shipping, Table;

	protected function bookingsRules($status)
	{
		if ($status == 'saved' || $status == 'shipped') {
            $get_total = 'delivered';
            $get_function = 'getDeliveredQuantity';
        }
        else {
            $get_total = 'booked';
            $get_function = 'getBookedQuantity';
        }

		return [$get_total, $get_function];
	}

	/*
		Questo serve a determinare quali valori prendere da prodotti e
		prenotazioni a seconda che siano state chieste delle quantità prenotato
		o consegnate
	*/
	protected static function offsetsByStatus($status)
	{
		if ($status == 'shipped') {
			return (object)[
				'alternate' => true,
				'by_variant' => 'delivered',
				'by_product' => 'delivered_pieces',
				'by_booking' => 'delivered',
			];
		}
		else {
			return (object)[
				'alternate' => false,
				'by_variant' => 'quantity',
				'by_product' => 'quantity_pieces',
				'by_booking' => 'booked',
			];
		}
	}

    protected function filterExtraModifiers($modifiers, $extras)
    {
        if ($extras == false) {
            $modifiers = $modifiers->filter(function($mod) {
                return is_null($mod->modifier->movementType);
            });
        }

        return $modifiers;
    }

	private function formatProduct($fields, $formattable, $product_redux, $product, $internal_offsets)
    {
		$ret = [];

        if (is_null($product_redux) == false) {
	        if (!empty($product_redux->variants)) {
	            $offset = $internal_offsets->by_variant;

	            foreach ($product_redux->variants as $variant) {
	                if ($variant->$offset == 0) {
	                    continue;
	                }

	                $row = [];
	                foreach($fields as $f) {
	                    if (isset($formattable[$f])) {
	                        if (isset($formattable[$f]->format_variant)) {
	                            $row[] = call_user_func($formattable[$f]->format_variant, $product, $variant, $internal_offsets->alternate);
	                        }
	                        else {
	                            $row[] = call_user_func($formattable[$f]->format_product, $product, $variant, $internal_offsets->alternate);
	                        }
	                    }
	                }

	                $ret[] = $row;
	            }

	            usort($ret, function($a, $b) {
	                return $a[0] <=> $b[0];
	            });
	        }
	        else {
	            $offset = $internal_offsets->by_product;
	            if ($product_redux->$offset != 0) {
		            $row = [];

		            foreach($fields as $f) {
		                if (isset($formattable[$f])) {
		                    $row[] = call_user_func($formattable[$f]->format_product, $product, $product_redux, $internal_offsets->alternate);
		                }
		            }

		            $ret[] = $row;
				}
	        }
		}

		return $ret;
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
