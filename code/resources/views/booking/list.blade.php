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
                            <input type="text" data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="{{ __('texts.generic.search.users') }}" />
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
					{{ __('texts.orders.help.changed_products') }}
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
    @include('booking.partials.staticgrid')
@endif
