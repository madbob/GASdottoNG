@php

$display_shipping_date = true;

switch($booking->status) {
    case 'shipped':
        $intro_text = __('texts.orders.booking_description.shipped');
        $display_shipping_date = false;
        $attribute = 'delivered';
        $get_value = 'delivered';
        break;

    case 'saved':
        $intro_text = __('texts.orders.booking_description.saved');
        $attribute = 'delivered';
        $get_value = 'delivered';
        break;

    case 'pending':
        $intro_text = __('texts.orders.booking_description.pending');
        $attribute = 'quantity';
        $get_value = 'booked';
        break;
}

$global_total = 0;
$bookings_tot = 0;

@endphp

@if(!empty($txt_message))
    <p>
        {!! nl2br($txt_message) !!}
    </p>

    <hr/>
@endif

<p>
    {{ $intro_text }}
</p>

@foreach($booking->bookings as $b)
    <?php

    $empty_booking = $b->products->isEmpty();
    if ($empty_booking) {
        foreach($b->friends_bookings as $fb) {
            $empty_booking = $fb->products->isEmpty();
            if ($empty_booking == false) {
                break;
            }
        }
    }

    if ($empty_booking) {
        continue;
    }

    $contacts = $b->order->enforcedContacts()->filter(fn($c) => filled($c->email));

    ?>

    <h3>{{ $b->order->supplier->printableName() }}</h3>

    <?php

    $bookings_tot++;
    $tot = $b->getValue('effective', false);
    $global_total += $tot;

    ?>

    @include('emails.bookingtable', ['booking' => $b, 'redux' => $redux, 'tot' => $tot])

    @if($b->friends_bookings->isEmpty() == false)
        <h5>{{ __('texts.orders.bookings_from_friends') }}</h5>

        @foreach($b->friends_bookings as $fb)
            <p>{{ $fb->user->printableName() }}</p>

            <?php

            $tot = $fb->getValue('effective', false);
            $global_total += $tot;

            ?>

            @include('emails.bookingtable', ['booking' => $fb, 'redux' => $redux, 'tot' => $tot])
        @endforeach
    @endif

    @if($contacts->isEmpty() == false)
        <br>
        <p>
            {{ __('texts.orders.communications_points') }}
        </p>
        <ul>
            @foreach($contacts as $contact)
                <li>{{ $contact->printableName() }} - {{ $contact->email }}</li>
            @endforeach
        </ul>
    @endif
@endforeach

@if($bookings_tot > 1)
    <br>
    <br>
    <p>
        {{ __('texts.orders.booking_total_amount', ['amount' => printablePriceCurrency($global_total)]) }}
    </p>
@endif

@if($display_shipping_date && $b && $b->order->shipping != null)
    <br>
    <br>
    <p>
        {{ __('texts.orders.formatted_delivery_date', ['date' => $b->order->printableDate('shipping')]) }}
    </p>
@endif
