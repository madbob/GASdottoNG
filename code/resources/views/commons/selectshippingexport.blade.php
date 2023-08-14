@if($currentgas->hasFeature('shipping_places'))
	<?php

	$options = [];

	foreach($included_metaplace as $imp) {
		switch($imp) {
			case 'no':
				$options['no'] = _i('Tutti');
				break;
			case 'all_by_name':
				$options['all_by_name'] = _i('Tutti (ordinati per utente)');
				break;
			case 'all_by_place':
				$options['all_by_place'] = _i('Tutti (ordinati per luogo)');
				break;
		}
	}

	foreach($currentgas->deliveries as $delivery) {
		$options[$delivery->id] = $delivery->name;
	}

	$shipping_warning = '';
	$test_no_shipping = [];

	foreach($aggregate->orders as $order) {
		$no_shipping = $order->bookings()->whereHas('user', function($query) {
			$query->doesntHave('shippingplace');
		})->get()->pluck('user_id')->toArray();

		$test_no_shipping = array_merge($test_no_shipping, $no_shipping);
		sort($test_no_shipping);
		$test_no_shipping = array_unique($test_no_shipping);
	}

	$test_no_shipping = count($test_no_shipping);

	if ($test_no_shipping > 0) {
		$shipping_warning = _i('Attenzione: %d utenti non hanno un luogo di consegna assegnato', [$test_no_shipping]);
	}

	?>
	<x-larastrap::radios name="shipping_place" :label="_i('Luogo di Consegna')" :options="$options" :value="$included_metaplace[0]" :help="$shipping_warning" />
@endif
