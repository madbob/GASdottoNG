{{ _i('Utente') }};{{ $has_shippings = ($currentgas->deliveries->isEmpty() == false) ? _i('Luogo di Consegna') . ';' : '' }}<?php foreach ($order->products as $product) {
    $total_price = 0;
    $total_transport = 0;
    $total_discount = 0;
    $all_products[$product->id] = 0;
    echo sprintf('%s (%s);', $product->printableName(), printablePriceCurrency($product->price, ','));
} ?>{{ _i('Totale Prezzo') }};{{ _i('Trasporto') }};{{ _i('Sconto') }};{{ _i('Utente') }}

@foreach($selected_bookings as $booking)
{{ $booking->user->printableName() }}{{ $has_shippings ? ';' . ($booking->user->shippingplace != null ? $booking->user->shippingplace->name : '') : ''  }}<?php foreach ($order->products as $product) {
    $quantity = $booking->$get_function($product, $get_function_real, true);
    $all_products[$product->id] += $quantity;
    echo ';' . printableQuantity($quantity, $product->measure->discrete, 3, ',');
} ?>;<?php $price = $booking->getValue($get_total, $with_friends); $total_price += $price; echo printablePrice($price, ',') ?>;<?php $transport = $booking->getValue('transport', $with_friends); $total_transport += $transport; echo printablePrice($transport, ',') ?>;<?php $discount = $booking->getValue('discount', $with_friends); $total_discount += $discount; echo printablePrice($discount, ',') ?>;{{ $booking->user->printableName() }}
@endforeach

TOTALI;{{ $has_shippings ? ';' : '' }}<?php foreach ($order->products as $product) {
    echo printableQuantity($all_products[$product->id], $product->measure->discrete, 3, ',') . ';';
} ?>{{ printablePrice($total_price, ',') }};{{ printablePrice($total_transport, ',') }};{{ printablePrice($total_discount, ',') }};TOTALI

{{ _i('Utente') }};{{ $has_shippings ? _i('Luogo di Consegna') . ';' : '' }}<?php foreach ($order->products as $product) {
    echo $product->printableName() . ';';
} ?>{{ _i('Totale Prezzo') }};{{ _i('Trasporto') }};{{ _i('Sconto') }};{{ _i('Utente') }}
