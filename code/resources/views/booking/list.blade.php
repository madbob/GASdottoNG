@if($aggregate->isRunning() == false)
    <?php $identifier = sprintf('booking-list-%s', $aggregate->id) ?>

    @if($aggregate->isActive())
        <div class="flowbox mb-1">
            <div>
                <x-larastrap::mbutton tlabel="generic.add_new" :triggers_modal="sprintf('addBooking-%s', $aggregate->id)" color="warning" />

				@include('commons.importcsv', [
					'modal_id' => 'importCSVdeliveries',
					'import_target' => 'deliveries',
					'modal_extras' => [
						'aggregate_id' => $aggregate->id,
					],
				])
            </div>

            <x-larastrap::modal classes="add-booking-while-shipping" :id="sprintf('addBooking-%s', $aggregate->id)">
                <div class="fillable-booking-space">
                    <div class="row">
                        <div class="col">
                            <input type="text" data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="{{ __('generic.search.users') }}" />
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col other-booking">
                        </div>
                    </div>
                </div>
            </x-larastrap::modal>

            @include('commons.iconslegend', [
                'class' => App\AggregateBooking::class,
                'target' => '#' . $identifier,
                'contents' => $aggregate->bookings,
            ])
        </div>
    @endif

	@if($aggregate->hasChangedProducts())
		<div class="row">
	        <div class="col">
				<div class="alert alert-danger mb-2">
					{{ __('orders.help.changed_products') }}
				</div>
			</div>
		</div>
	@endif

    <div class="row">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => $identifier,
                'items' => $aggregate->bookings,
                'url' => url('delivery/' . $aggregate->id . '/user'),
                'extra_data' => [
                    'data-sorting-function' => 'sortShippingBookings'
                ]
            ])
        </div>
    </div>
@else
    <div class="alert alert-danger mb-3">
        {{ __('orders.help.waiting_closing_for_deliveries') }}
    </div>

    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('user.name') }}</th>
                            <th scope="col">{{ __('generic.created_at') }}</th>
                            <th scope="col">{{ __('generic.updated_at') }}</th>
                            <th scope="col">{{ __('orders.totals.booked') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php

                        $bookings = $aggregate->bookings;
                        usort($bookings, function($a, $b) {
                            return $a->user->printableName() <=> $b->user->printableName();
                        });

                        @endphp

                        @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->user->printableName() }}</td>
                                <td>{{ printableDate($booking->created_at) }}</td>
                                <td>{{ printableDate($booking->updated_at) }}</td>
                                <td>{{ printablePriceCurrency($booking->getValue('booked', true)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
