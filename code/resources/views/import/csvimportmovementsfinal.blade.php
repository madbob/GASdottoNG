<x-larastrap::modal :title="_i('Importa CSV')" :buttons="[['color' => 'success', 'label' => _i('Chiudi'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

	<table class="table">
		<thead>
			<tr>
				<th scope="col">{{ _i('Tipo Movimento Contabile') }}</th>
				<th scope="col">{{ _i('Metodo') }}</th>
				<th scope="col">{{ _i('Data') }}</th>
				<th scope="col">{{ _i('Valore') }}</th>
				<th scope="col">{{ _i('Pagante') }}</th>
				<th scope="col">{{ _i('Pagato') }}</th>
                <th scope="col">{{ _i('Identificativo') }}</th>
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
