{{ _i('Utente') }};<?php foreach ($order->products as $product) {
    $total_price = 0;
    $total_transport = 0;
    $all_products[$product->id] = 0;
    echo $product->printableName() . ';';
} ?>{{ _i('Prezzo Totale') }};{{ _i('Trasporto') }};{{ _i('Utente') }}

@foreach($selected_bookings as $booking)
{{ $booking->user->printableName() }}<?php foreach ($order->products as $product) {
    $quantity = $booking->$get_function($product, $get_function_real);
    $all_products[$product->id] += $quantity;
    echo ';' . printableQuantity($quantity, $product->measure->discrete, 3, ',');
} ?>;<?php $price = $booking->value; $total_price += $price; echo printablePrice($price, ',') ?>;<?php $transport = $booking->transport; $total_transport += $transport; echo printablePrice($transport, ',') ?>;{{ $booking->user->printableName() }}
@endforeach

TOTALI;<?php foreach ($order->products as $product) {
    echo printableQuantity($all_products[$product->id], $product->measure->discrete, 3, ',') . ';';
} ?>{{ printablePrice($total_price, ',') }};{{ printablePrice($total_transport, ',') }};TOTALI

{{ _i('Utente') }};<?php foreach ($order->products as $product) {
    echo $product->printableName() . ';';
} ?>{{ _i('Prezzo Totale') }};{{ _i('Trasporto') }};{{ _i('Utente') }}
