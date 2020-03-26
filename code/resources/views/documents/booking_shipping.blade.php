<?php

if ($with_friends == true)
    $products_source = 'products_with_friends';
else
    $products_source = 'products';

$transport = $booking->getValue('transport', true);

?>

@foreach($booking->$products_source as $product)
    @if($product->variants->isEmpty() == false)
        @foreach($product->variants as $variant)
            @if(!empty($variant->quantity))
                <tr>
                    <td width="40%">{{ $product->product->printableName() }}</td>
                    <td width="40%">{{ printableQuantity($variant->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }} {{ $variant->printableName() }}</td>
                    <td width="20%">{{ printablePriceCurrency($variant->quantityValue(), ',') }}</td>
                </tr>
            @endif
        @endforeach
    @else
        @if(!empty($product->quantity))
            <tr>
                <td width="40%">{{ $product->product->printableName() }}</td>
                <td width="40%">{{ printableQuantity($product->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }}</td>
                <td width="20%">{{ printablePriceCurrency($product->quantityValue(), ',') }}</td>
            </tr>
        @endif
    @endif
@endforeach

@if($transport > 0)
    <tr>
        <td width="40%">{{ _('Trasporto') }}</td>
        <td width="40%">&nbsp;</td>
        <td width="20%">{{ printablePriceCurrency($transport, ',') }}</td>
    </tr>
@endif
