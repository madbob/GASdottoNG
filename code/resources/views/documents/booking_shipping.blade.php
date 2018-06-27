@foreach($booking->$products_source as $product)
    @if($product->variants->isEmpty() == false)
        @foreach($product->variants as $variant)
            <tr>
                <td width="40%">{{ $product->product->printableName() }}</td>
                <td width="40%">{{ printableQuantity($variant->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }} {{ $variant->printableName() }}</td>
                <td width="20%">{{ printablePriceCurrency($variant->quantityValue(), ',') }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td width="40%">{{ $product->product->printableName() }}</td>
            <td width="40%">{{ printableQuantity($product->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }}</td>
            <td width="20%">{{ printablePriceCurrency($product->quantityValue(), ',') }}</td>
        </tr>
    @endif
@endforeach

@if($booking->check_transport != 0)
    <tr>
        <td width="40%">{{ _i('Trasporto') }}</td>
        <td width="40%">&nbsp;</td>
        <td width="20%">{{ printablePriceCurrency($booking->check_transport) }}</td>
    </tr>
@endif
