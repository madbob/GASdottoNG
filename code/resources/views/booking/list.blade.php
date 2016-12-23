<?php $identifier = sprintf('booking-list-', str_random(5)) ?>

@include('commons.iconslegend', ['class' => 'AggregateBooking', 'target' => '#' . $identifier])

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', ['identifier' => $identifier, 'items' => $aggregate->bookings, 'url' => url('delivery/' . $aggregate->id . '/user')])
    </div>
</div>
