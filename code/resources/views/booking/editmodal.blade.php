<x-larastrap::modal :title="_i('Modifica Prenotazione')" size="fullscreen">
	@include('booking.edit', [
		'aggregate' => $aggregate,
		'user' => $user,
	])
</x-larastrap::modal>
