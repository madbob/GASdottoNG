<html>
    <body>
        <h3>Dettaglio Consegne Ordine {{ $order->internal_number }} presso {{ $order->supplier->printableName() }} del {{ date('d/m/Y') }}</h3>

        <hr/>

        @foreach($order->bookings as $booking)
            <table border="1" style="width: 100%" cellpadding="5">
                <thead>
                    <tr>
                        <th colspan="3">{{ $booking->user->printableName() }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->products as $product)
                        @if($product->variants->isEmpty() == false)
                            @foreach($product->variants as $variant)
                                <tr>
                                    <td width="40%">{{ $product->product->printableName() }}</td>
                                    <td width="40%">{{ $variant->quantity }} {{ $product->product->printableMeasure(true) }} {{ $variant->printableName() }}</td>
                                    <td width="20%">{{ printablePrice($variant->quantityValue()) }} €</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td width="40%">{{ $product->product->printableName() }}</td>
                                <td width="40%">{{ $product->quantity }} {{ $product->product->printableMeasure(true) }}</td>
                                <td width="20%">{{ printablePrice($product->quantityValue()) }} €</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totale: {{ printablePrice($booking->total_value) }} €</th>
                    </tr>
                </tfoot>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
