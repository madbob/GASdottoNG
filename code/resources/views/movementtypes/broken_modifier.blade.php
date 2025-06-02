<div class="alert alert-danger">
	@if($id == 'booking-payment')
		<p>
			{{ __('movements.help.missing_method_for_movements_in_modifiers') }}
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
			{{ __('movements.help.missing_method_for_movement_in_modifier', ['name' => movementTypes('booking-payment')->name]) }}
		</p>
	@endif
</div>
