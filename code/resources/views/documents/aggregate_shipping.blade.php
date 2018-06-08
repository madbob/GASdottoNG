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
            @if($super_booking->total_value == 0)
                @continue
            @endif

            @if($shipping_place != 0 && $super_booking->user->preferred_delivery_id != $shipping_place)
                @continue
            @endif

            <?php $cell_value = 0 ?>

            <table border="1" style="width: 100%" cellpadding="5" nobr="true">
                <tr>
                    <th colspan="3"><strong>{{ $super_booking->user->printableName() }}

                        <?php

                        $contacts = [];

                        foreach($super_booking->user->contacts as $contact) {
                            if ($contact->type == 'phone' || $contact->type == 'mobile')
                                $contacts[] = $contact->value;
                        }

                        if (!empty($contacts))
                            echo ' - ' . join(', ', $contacts);

                    ?></strong></th>
                </tr>

                @foreach($super_booking->bookings as $booking)
                    @if($booking->$products_source->isEmpty() == false)
                        <?php $cell_value += $booking->total_value ?>

                        <tr>
                            <td colspan="3"><strong>{{ $booking->order->supplier->printableName() }}</strong></td>
                        </tr>

                        @include('documents.booking_shipping', [
                            'booking' => $booking,
                            'products_source' => $products_source
                        ])
                    @endif
                @endforeach

                <tr>
                    <th colspan="3"><strong>{{ _i('Totale') }}: {{ printablePriceCurrency($cell_value, ',') }}</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
