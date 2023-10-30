<x-larastrap::modal :title="_i('Importa consegne')" size="fullscreen">
    <div class="wizard_page">
        <x-larastrap::wizardform :action="url('import/csv?type=deliveries&step=run')">
			<input type="hidden" name="aggregate_id" value="{{ $aggregate_id }}">
			<input type="hidden" name="order_id" value="{{ $order_id }}">
			<input type="hidden" name="data" value="{{ json_encode($data) }}">

			<x-larastrap::radios name="action" :label="_i('Comportamento')" :options="['save' => _i('Assegna le quantità come salvate ma non chiudere le consegne'), 'close' => _i('Marca le prenotazioni come consegnate e genera i movimenti contabili di pagamento')]" value="save" />

            @include('import.errors', ['errors' => $errors])

			<hr />

            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _i('Utente') }}</th>
                        <th>{{ _i('Totale') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $index => $booking)
                        <tr>
                            <td>
								<input type="hidden" name="user[]" value="{{ $booking->user_id }}">
                                {{ $booking->user_name }}
                            </td>
                            <td>
                                {{ printablePriceCurrency($booking->total) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
