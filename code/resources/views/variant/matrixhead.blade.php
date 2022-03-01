<thead>
	<tr>
		@foreach($combos->first()->values as $value)
			<th>{{ $value->variant->name }}</th>
		@endforeach

		<th width="15%">{{ _i('Ordinabile') }}</th>

		<th width="20%">
			{{ _i('Codice Fornitore') }}
			<x-larastrap::pophelp :text="_i('Se non viene specificato, tutte le varianti usano il Codice Fornitore del prodotto principale.')" />
		</th>
		<th width="20%">
			{{ _i('Differenza Prezzo') }}
			<x-larastrap::pophelp :text="_i('Differenza di prezzo, positiva o negativa, da applicare al prezzo del prodotto quando una specifica combinazione di varianti viene selezionata.')" />
		</th>

		@if($product->measure->discrete)
			<th width="20%">{{ _i('Differenza Peso') }}</th>
		@endif
	</tr>
</thead>
