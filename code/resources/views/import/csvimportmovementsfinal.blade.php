<x-larastrap::modal :title="_i('Importa CSV')" :buttons="[['color' => 'success', 'label' => _i('Chiudi'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

	<table class="table">
		<thead>
			<tr>
				<th>{{ _i('Tipo Movimento Contabile') }}</th>
				<th>{{ _i('Metodo') }}</th>
				<th>{{ _i('Data') }}</th>
				<th>{{ _i('Valore') }}</th>
				<th>{{ _i('Pagante') }}</th>
				<th>{{ _i('Pagato') }}</th>
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
				</tr>
			@endforeach
		</tbody>
	</table>

    @if(!empty($errors))
        <hr/>

        <p>
            {{ _i('Errori') }}:
        </p>

        <ul class="list-group">
            @foreach($errors as $e)
                <li class="list-group-item">{!! $e !!}</li>
            @endforeach
        </ul>
    @endif
</x-larastrap::modal>
