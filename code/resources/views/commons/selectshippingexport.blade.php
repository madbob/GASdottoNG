@php

$actual_groups = [];

$all = $aggregate->circlesByGroup();
$all = array_filter($all, fn($a) => $a->group->context != 'order');

if (empty($all) == false) {
	$options = [];

	/*
		Se ho più di un raggruppamento possibile, piazzo la selezione per
		l'ordinamento (per utente o per Circle) a parte e vale per l'intera
		selezione
	*/
	if (count($all) > 1) {
		foreach($included_metaplace as $imp) {
			switch($imp) {
				case 'no':
					$options['all_by_name'] = __('generic.all');
					break;
				case 'all_by_name':
					$options['all_by_name'] = _i('Utente');
					break;
				case 'all_by_place':
					$options['all_by_place'] = _i('Aggregazioni/Gruppi');
					break;
                case 'specific':
                    foreach($all as $meta) {
                        if ($meta->group->context == 'user') {
                            $options['group_' . $meta->group->id] = $meta->group->name;
                        }
                    }

                    break;
			}
		}

		$actual_groups[_i('Ordina per')] = (object) [
			'id' => 'master_sorting',
			'options' => $options,
			'help' => '',
		];

		$global_options = ['all_by_name' => __('generic.all')];
	}
	else {
		foreach($included_metaplace as $imp) {
			switch($imp) {
				case 'no':
					$options['all_by_name'] = __('generic.all');
					break;
				case 'all_by_name':
					$options['all_by_name'] = _i('Tutti (ordinati per utente)');
					break;
				case 'all_by_place':
					$options['all_by_place'] = _i('Tutti (ordinati per gruppo)');
					break;
			}
		}

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
				$shipping_warning = _i('Attenzione: %d utenti non hanno un gruppi assegnato per %s', [$test_no_shipping, $meta->group->printableName()]);
			}
		}

		$actual_circles = [];
		foreach($meta->circles as $cir) {
			$actual_circles[$cir->id] = $cir->printableName();
		}

		$actual_groups[$meta->group->name] = (object) [
			'id' => $meta->group->id,
			'options' => array_merge($global_options, $actual_circles),
			'help' => $shipping_warning,
		];
	}
}

@endphp

@foreach($actual_groups as $label => $meta)
	<x-larastrap::radios :name="sprintf('circles_%s', $meta->id)" :label="$label" :options="$meta->options" :help="$meta->help" required />
@endforeach
