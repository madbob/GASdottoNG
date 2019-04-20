<html>
    <head>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }
        </style>
    </head>

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

            <table border="1" style="width: 100%" cellpadding="5" nobr="true">
                <tr>
                    <th colspan="3">
                        <?php

                        $head = '';

                        if($shipping_mode == 'all_by_place')
                            $head .= ($super_booking->user->shippingplace ? $super_booking->user->shippingplace->name : _i('[Luogo non Specificato]')) . ' - ';

                        $head .= $super_booking->user->printableName();

                        $contacts = [];

                        foreach($super_booking->user->contacts as $contact) {
                            if ($contact->type == 'phone' || $contact->type == 'mobile')
                                $contacts[] = $contact->value;
                        }

                        if (!empty($contacts))
                            $head .= ' - ' . join(', ', $contacts);

                        ?>
                        <strong>{{ $head }}</strong>
                    </th>
                </tr>

                @foreach($super_booking->bookings as $booking)
                    @if($booking->products_with_friends->isEmpty() == false)
                        <tr>
                            <td colspan="3"><strong>{{ $booking->order->supplier->printableName() }}</strong></td>
                        </tr>

                        @include('documents.booking_shipping', [
                            'booking' => $booking,
                            'with_friends' => true
                        ])
                    @endif
                @endforeach

                <tr>
                    <th colspan="3"><strong>{{ _i('Totale') }}: {{ printablePriceCurrency($super_booking->total_value, ',') }}</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
