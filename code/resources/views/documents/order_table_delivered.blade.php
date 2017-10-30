Utente;<?php foreach ($order->products as $product) {
    echo $product->printableName() . ';';
} ?>Prezzo Totale;Trasporto;Utente

@foreach($order->bookings as $booking)
{{ $booking->user->printableName() }}<?php foreach ($order->products as $product) {
    echo ';' . printableQuantity($booking->getDeliveredQuantity($product), $product->measure->discrete, 3, ',');
} ?>;{{ printablePrice($booking->delivered, ',') }};{{ printablePrice($booking->transport, ',') }};{{ $booking->user->printableName() }}
@endforeach

<?php foreach ($order->products as $product) {
    echo ';'.$product->printableName();
} ?>
