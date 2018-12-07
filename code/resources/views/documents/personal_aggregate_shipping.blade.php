<html>
    <body>
        <h3>{{ _i('Dettaglio Consegne') }}<br/>
            @if($aggregate->orders()->count() <= App\Aggregate::aggregatesConvenienceLimit())
                @foreach($aggregate->orders as $order)
                    {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
                @endforeach
            @endif
        </h3>

        @foreach($bookings as $super_booking)
            @if($super_booking->total_value == 0 && $super_booking->total_delivered == 0)
                @continue
            @endif

            <?php $booked_cell_value = $delivered_cell_value = 0 ?>

            <table border="1" style="width: 100%" cellpadding="5" nobr="true">
                <tr>
                    <th colspan="3">
                        <strong>{{ $super_booking->user->printableName() }}</strong>
                    </th>
                </tr>

                @foreach($super_booking->bookings as $booking)
                    @if($booking->products->isEmpty() == false)
                        <?php

                        $booked_cell_value += $booking->total_value;
                        $delivered_cell_value += $booking->total_delivered;

                        ?>

                        <tr>
                            <td colspan="5"><strong>{{ $booking->order->supplier->printableName() }}</strong></td>
                        </tr>

                        <tr>
                            <td width="40%"><strong>{{ _i('Prodotto') }}</strong></td>
                            <td width="15%"><strong>{{ _i('Prenotato') }}</strong></td>
                            <td width="15%">&nbsp;</td>
                            <td width="15%"><strong>{{ _i('Consegnato') }}</strong></td>
                            <td width="15%">&nbsp;</td>
                        </tr>

                        @foreach($booking->products as $product)
                            @if($product->variants->isEmpty() == false)
                                @foreach($product->variants as $variant)
                                    <tr>
                                        <td>{{ $product->product->printableName() }}</td>
                                        <td>{{ printableQuantity($variant->quantity, $product->product->measure->discrete, 2, ',') }} {{ $variant->printableName() }}</td>
                                        <td>{{ printablePriceCurrency($variant->quantityValue(), ',') }}</td>
                                        <td>{{ printableQuantity($variant->delivered, $product->product->measure->discrete, 2, ',') }} {{ $variant->printableName() }}</td>
                                        <td>{{ printablePriceCurrency($variant->deliveredValue(), ',') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ $product->product->printableName() }}</td>
                                    <td>{{ printableQuantity($product->quantity, $product->product->measure->discrete, 2, ',') }}</td>
                                    <td>{{ printablePriceCurrency($product->quantityValue(), ',') }}</td>
                                    <td>{{ printableQuantity($product->delivered, $product->product->measure->discrete, 2, ',') }}</td>
                                    <td>{{ printablePriceCurrency($product->deliveredValue(), ',') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if($booking->check_transport != 0)
                    <tr>
                        <th colspan="5"><strong>{{ _i('Trasporto') }}: {{ printablePriceCurrency($booking->check_transport, ',') }}</th>
                    </tr>
                @endif

                <tr>
                    <th colspan="5"><strong>{{ _i('Totale Prenotato') }}: {{ printablePriceCurrency($booked_cell_value, ',') }}</strong></th>
                </tr>
                <tr>
                    <th colspan="5"><strong>{{ _i('Totale Consegnato') }}: {{ printablePriceCurrency($delivered_cell_value, ',') }}</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
