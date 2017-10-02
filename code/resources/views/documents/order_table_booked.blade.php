<?php foreach ($order->products as $product) {
    echo ';'.$product->printableName();
} ?>

@foreach($order->bookings as $booking)
{{ $booking->user->printableName() }}<?php foreach ($order->products as $product) {
    echo ';' . printableQuantity($booking->getBookedQuantity($product, true), $product->measure->discrete, 3, ',');
} ?>;{{ printablePrice($booking->value, ',') }};{{ $booking->user->printableName() }}
@endforeach

<?php foreach ($order->products as $product) {
    echo ';'.$product->printableName();
} ?>
