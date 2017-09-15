<?php $identifier = sprintf('booking-list-', str_random(5)) ?>

<button class="btn btn-default" data-toggle="modal" data-target="#addBooking-{{ $aggregate->id }}">Aggiungi Utente</button>
<div class="modal fade add-booking-while-shipping" id="addBooking-{{ $aggregate->id }}" tabindex="-1">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Aggiungi Utente</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="Cerca Utente" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 other-booking">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('commons.iconslegend', ['class' => 'AggregateBooking', 'target' => '#' . $identifier])

<div class="row">
    <div class="col-md-12">
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
