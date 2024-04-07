@php

$actual_groups = [];

$all = $aggregate->circlesByGroup();
$all = array_filter($all, fn($a) => $a->group->context != 'order');

if (empty($all) == false) {
	$options = [];

	foreach($included_metaplace as $imp) {
		switch($imp) {
			case 'no':
				$options['all_by_name'] = _i('Tutti');
				break;
			case 'all_by_name':
				$options['all_by_name'] = _i('Tutti (ordinati per utente)');
				break;
			case 'all_by_place':
				$options['all_by_place'] = _i('Tutti (ordinati per cerchia)');
				break;
		}
	}

	/*
		Se ho più di un raggruppamento possibile, piazzo la selezione per
		l'ordinamento (per utente o per Circle) a parte e vale per l'intera
		selezione
	*/
	if (count($all) > 1) {
		$actual_groups[_i('Ordina per')] = (object) [
			'options' => $options,
			'help' => '',
		];

		$global_options = [-1 => _i('Tutti')];
	}
	else {
		$global_options = $options;
	}

	foreach($all as $meta) {
		$shipping_warning = '';

		if ($meta->group->context == 'user') {
			$test_no_shipping = [];

			foreach($aggregate->orders as $order) {
				$no_shipping = $order->bookings()->whereHas('user', function($query) use ($meta) {
					$query->whereDoesntHave('circles', function($query) use ($meta) {
						$query->where('group_id', $meta->group->id);
					});
				})->get()->pluck('user_id')->toArray();

				$test_no_shipping = array_merge($test_no_shipping, $no_shipping);
				sort($test_no_shipping);
				$test_no_shipping = array_unique($test_no_shipping);
			}

			$test_no_shipping = count($test_no_shipping);

			if ($test_no_shipping > 0) {
				$shipping_warning = _i('Attenzione: %d utenti non hanno una cerchia assegnata', [$test_no_shipping]);
			}
		}

		$actual_groups[$meta->group->name] = (object) [
			'options' => array_merge($global_options, array_map(fn($c) => $c->name, $meta->circles)),
			'help' => $shipping_warning,
		];
	}
}

@endphp

@foreach($actual_groups as $label => $meta)
	<x-larastrap::radios name="circles[]" :label="$label" :options="$meta->options" :value="array_key_first($meta->options)" :help="$meta->help" />
@endforeach
