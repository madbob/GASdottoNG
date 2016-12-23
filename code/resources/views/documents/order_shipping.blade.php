<html>
    <body>
        <h3>Dettaglio Consegne {{ $order->supplier->printableName() }} del {{ date('d/m/Y') }}</h3>

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
                        <tr>
                            <td width="40%">{{ $product->product->printableName() }}</td>
                            <td width="40%">{{ $product->quantity }} {{ $product->product->printableMeasure() }}</td>
                            <td width="20%">{{ printablePrice($product->quantityValue()) }} €</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totale: {{ printablePrice($booking->value) }} €</th>
                    </tr>
                </tfoot>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
