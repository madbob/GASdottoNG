<?php

$display_shipping_date = true;

switch($booking->status) {
    case 'shipped':
        $intro_text = _i('Di seguito il riassunto dei prodotti che ti sono stati consegnati:');
        $display_shipping_date = false;
        $attribute = 'delivered';
        $get_value = 'delivered';
        break;

    case 'saved':
        $intro_text = _i('Di seguito il riassunto dei prodotti che ti saranno consegnati:');
        $attribute = 'delivered';
        $get_value = 'delivered';
        break;

    case 'pending':
        $intro_text = _i('Di seguito il riassunto dei prodotti che hai ordinato:');
        $attribute = 'quantity';
        $get_value = 'booked';
        break;
}

$global_total = 0;
$bookings_tot = 0;

?>

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
        <h5>{{ _i('Gli ordini dei tuoi amici') }}</h5>

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
            {{ _i("Per comunicazioni su quest'ordine, si raccomanda di contattare:") }}
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
        {{ _i('Totale da pagare: %s', [printablePriceCurrency($global_total)]) }}
    </p>
@endif

@if($display_shipping_date && $b && $b->order->shipping != null)
    <br>
    <br>
    <p>
        {{ _i('La consegna avverrà %s.', [$b->order->printableDate('shipping')]) }}
    </p>
@endif
