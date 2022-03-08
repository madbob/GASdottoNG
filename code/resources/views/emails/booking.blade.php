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
    <?php $variable = false ?>

    <h3>{{ $b->order->supplier->printableName() }}</h3>
    @include('emails.bookingtable', ['booking' => $b, 'redux' => $redux])

    @if($b->friends_bookings->isEmpty() == false)
        <h5>{{ _i('Gli ordini dei tuoi amici') }}</h5>

        @foreach($b->friends_bookings as $fb)
            <p>{{ $fb->user->printableName() }}</p>
            @include('emails.bookingtable', ['booking' => $fb, 'redux' => $redux])
        @endforeach
    @endif

    <br>

    @if($display_shipping_date && $variable)
        <p>
            {{ _i("L'importo reale di questo ordine dipende dal peso effettivo dei prodotti consegnati; il totale qui riportato è solo indicativo.") }}
        </p>
    @endif

    <p>
        {{ _i("Per comunicazioni su quest'ordine, si raccomanda di contattare:") }}
    </p>
    <ul>
        @foreach($b->order->enforcedContacts() as $contact)
            <li>{{ $contact->printableName() }} - {{ $contact->email }}</li>
        @endforeach
    </ul>
@endforeach

@if($bookings_tot > 1)
    <p>
        {{ _i('Totale da pagare: %s', [printablePriceCurrency($global_total)]) }}
    </p>
@endif

@if($display_shipping_date && $b && $b->order->shipping != null)
    <p>
        {{ _i('La consegna avverrà %s.', [$b->order->printableDate('shipping')]) }}
    </p>
@endif
