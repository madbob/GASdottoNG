<?php

if ($with_friends) {
    $products_source = 'products_with_friends';
    $modifiers = $booking->aggregatedModifiersWithFriends();
}
else {
    $products_source = 'products';
    $modifiers = $booking->aggregatedModifiers();
}

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
                <td width="20%">{{ printablePriceCurrency($product->getValue('booked'), ',') }}</td>
            </tr>
        @endif
    @endif
@endforeach

@foreach($modifiers as $am)
    <tr>
        <td width="40%">{{ $am->name }}</td>
        <td width="40%">&nbsp;</td>
        <td width="20%">{{ App\ModifiedValue::printAggregated($am) }}</td>
    </tr>
@endforeach
