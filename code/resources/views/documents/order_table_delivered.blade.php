<?php foreach ($order->products as $product) {
    echo ';'.$product->printableName();
} ?>

@foreach($order->bookings as $booking)
{{ $booking->user->printableName() }}<?php foreach ($order->products as $product) {
    echo ';'.$booking->getDeliveredQuantity($product, true);
} ?>;{{ $booking->delivered }};{{ $booking->user->printableName() }}
@endforeach

<?php foreach ($order->products as $product) {
    echo ';'.$product->printableName();
} ?>
