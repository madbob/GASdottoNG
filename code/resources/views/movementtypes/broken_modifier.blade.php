<div class="alert alert-danger">
	@if($id == 'booking-payment')
		<p>
			{!! _i('Attenzione! Ci sono tipi di movimento contabile associati a modificatori per i quali non è stato definito un comportamento per tutti i metodi di pagamento abilitati per le consegne.') !!}
		</p>
		<p>
			{!! _i('Si raccomanda di revisionarli, o potrebbero non essere correttamente applicati ai rispettivi modificatori (con potenziale perdita di informazioni in contabilità).') !!}
		</p>
		<ul>
			@foreach(movementTypes() as $type)
				@if($type->id != 'booking-payment' && $type->hasBrokenModifier())
					<li>{{ $type->name }}</li>
				@endif
			@endforeach
		</ul>
	@else
		<p>
			{!! _i('Attenzione! Questo tipo di movimento contabile è associato ad almeno un modificatore, ma non ha un comportamento definito per tutti i metodi di pagamento abilitati per il tipo movimento "%s".', [movementTypes('booking-payment')->name]) !!}
		</p>
		<p>
			{!! _i('Si raccomanda di revisionarlo, o non sarà correttamente applicato al modificatore (con potenziale perdita di informazioni in contabilità).') !!}
		</p>
	@endif
</div>
