<x-larastrap::modal :buttons="[['color' => 'success', 'label' => __('generic.close'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

	<table class="table">
		<thead>
			<tr>
				<th scope="col">{{ __('movements.type') }}</th>
				<th scope="col">{{ __('generic.method') }}</th>
				<th scope="col">{{ __('generic.date') }}</th>
				<th scope="col">{{ __('generic.value') }}</th>
				<th scope="col">{{ __('movements.paying') }}</th>
				<th scope="col">{{ __('movements.payed') }}</th>
                <th scope="col">{{ __('generic.identifier') }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach($objects as $m)
				<tr>
					<td>{{ $m->printableType() }}</td>
					<td>{{ $m->printablePayment() }}</td>
					<td>{{ $m->printableDate('date') }}</td>
					<td>{{ printablePriceCurrency($m->amount, '.', $m->currency) }}</td>

					<td>
						@if($m->sender)
							{{ $m->sender->printableName() }}
						@endif
					</td>

					<td>
						@if($m->target)
							{{ $m->target->printableName() }}
						@endif
					</td>

                    <td>{{ $m->identifier }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

    @include('import.errors', ['errors' => $errors])
</x-larastrap::modal>
