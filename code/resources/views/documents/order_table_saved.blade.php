Utente;<?php foreach ($order->products as $product) {
    echo $product->printableName() . ';';
} ?>Prezzo Totale;Trasporto;Utente

@foreach($order->bookings()->where('status', 'saved')->get() as $booking)
{{ $booking->user->printableName() }}<?php foreach ($order->products as $product) {
    echo ';' . printableQuantity($booking->getBookedQuantity($product, true), $product->measure->discrete, 3, ',');
} ?>;{{ printablePrice($booking->value, ',') }};{{ $booking->user->printableName() }}
@endforeach

Utente;<?php foreach ($order->products as $product) {
    echo $product->printableName() . ';';
} ?>Prezzo Totale;Trasporto;Utente
