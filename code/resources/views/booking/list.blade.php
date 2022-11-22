@if($aggregate->isRunning() == false)
    <?php $identifier = sprintf('booking-list-%s', $aggregate->id) ?>

    @if($aggregate->isActive())
        <div class="flowbox mb-1">
            <x-larastrap::mbutton :label="_i('Aggiungi Utente')" :triggers_modal="sprintf('addBooking-%s', $aggregate->id)" color="warning" />
            <x-larastrap::modal :title="_i('Aggiungi Utente')" classes="add-booking-while-shipping" :id="sprintf('addBooking-%s', $aggregate->id)">
                <div class="fillable-booking-space">
                    <div class="row">
                        <div class="col">
                            <input type="text" data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="{{ _i('Cerca Utente') }}" />
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col other-booking">
                        </div>
                    </div>
                </div>
            </x-larastrap::modal>

            @include('commons.iconslegend', ['class' => 'AggregateBooking', 'target' => '#' . $identifier])
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
        {{ _i('Questo pannello sarà attivo quando tutte le prenotazioni saranno chiuse') }}
    </div>

    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ _i('Utente') }}</th>
                            <th>{{ _i('Data Creazione') }}</th>
                            <th>{{ _i('Ultima Modifica') }}</th>
                            <th>{{ _i('Totale Prenotato') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aggregate->bookings as $booking)
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
