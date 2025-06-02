<x-larastrap::modal size="fullscreen">
    <div class="wizard_page">
        <x-larastrap::wizardform :action="url('import/csv?type=deliveries&step=run')">
			<input type="hidden" name="aggregate_id" value="{{ $aggregate_id }}">
			<input type="hidden" name="order_id" value="{{ $order_id }}">
			<input type="hidden" name="data" value="{{ json_encode($data) }}">

			<x-larastrap::radios name="action" tlabel="generic.behavior" :options="[
                'save' => __('orders.importing.save'),
                'close' => __('orders.importing.close')
            ]" value="save" />

            @include('import.errors', ['errors' => $errors])

			<hr />

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('user.name') }}</th>
                        <th scope="col">{{ __('orders.totals.total') }}</th>
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
