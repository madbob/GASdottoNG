<table style="width:100%" border="1" cellpadding="5px">
	<thead>
		<th scope="col" style="width:50%; text-align: left">{{ _i('Prodotto') }}</th>
		<th scope="col" style="width:25%; text-align: left">{{ _i('Quantità') }}</th>
		<th scope="col" style="width:25%; text-align: left">{{ _i('Prezzo') }}</th>
	</thead>

	<tbody>
		@foreach($booking->products as $product)
			@if($product->$attribute != 0)
				@if($product->variants->isEmpty() == false)
					<tr>
						<td>
							@php

							$row = [$product->product->printableName()];
							foreach($product->variants as $variant) {
								$row[] = $variant->printableName();
							}

							$row = join('<br>', $row);

							@endphp

							{!! $row !!}
						</td>
						<td>
							@php

							$row = ['&nbsp;'];
							foreach($product->variants as $variant) {
								$row[] = sprintf('%s %s', $variant->$attribute, $product->product->printableMeasure());
							}

							$row = join('<br>', $row);

							@endphp

							{!! $row !!}
						</td>
						<td>
							{{ printablePriceCurrency($product->getValue($get_value)) }}
						</td>
					</tr>
				@else
					<tr>
						<td>{{ $product->product->printableName() }}</td>
						<td>{{ $product->$attribute }} {{ $product->product->printableMeasure() }}</td>
						<td>{{ printablePriceCurrency($product->getValue($get_value)) }}</td>
					</tr>
				@endif
			@endif
		@endforeach

		<?php

		$modifiers = $booking->applyModifiers($redux);
		$aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);

		?>

		@foreach($aggregated_modifiers as $am)
			<tr>
				<td><strong>{{ $am->name }}</strong></td>
				<td>&nbsp;</td>
				<td>{{ printablePriceCurrency($am->amount) }}</td>
			</tr>
		@endforeach

		<tr>
			<td><strong>{{ _i('Totale') }}</strong></td>
			<td>&nbsp;</td>
			<td>{{ printablePriceCurrency($tot) }}</td>
		</tr>
	</tbody>
</table>
