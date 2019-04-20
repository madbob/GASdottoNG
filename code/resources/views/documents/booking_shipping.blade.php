<?php

if ($with_friends == true)
    $products_source = 'products_with_friends';
else
    $products_source = 'products';

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

@if($booking->getValue('transport', $with_friends) != 0)
    <tr>
        <td width="40%">{{ _i('Trasporto') }}</td>
        <td width="40%">&nbsp;</td>
        <td width="20%">{{ printablePriceCurrency($booking->getValue('transport', $with_friends), ',') }}</td>
    </tr>
@endif
