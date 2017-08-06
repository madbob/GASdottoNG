<?php $summary = $order->calculateSummary(); ?>Nome;Quantità Totale;Prezzo Totale;Trasporto
@foreach($order->supplier->products as $product)
@if($order->hasProduct($product))
@if(isset($summary->by_variant[$product->id]))
@foreach($summary->by_variant[$product->id] as $name => $variant)
@if($variant['quantity'] != 0)
{{ $product->printableName() }} {{ $name }};{{ $variant['quantity'] }};{{ printablePrice($variant['price']) }};{{ printablePrice($summary->products[$product->id]['transport']) }}
@endif
@endforeach
@else
@if($summary->products[$product->id]['quantity'])
{{ $product->printableName() }};{{ $summary->products[$product->id]['quantity'] }};{{ printablePrice($summary->products[$product->id]['price']) }};{{ printablePrice($summary->products[$product->id]['transport']) }}
@endif
@endif
@endif
@endforeach
