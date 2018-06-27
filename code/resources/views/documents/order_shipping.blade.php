<html>
    <body>
        <h3>{{ _i('Dettaglio Consegne Ordine %s presso %s del %s', [$order->internal_number, $order->supplier->printableName(), $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y')]) }}</h3>

        <hr/>

        @foreach($bookings as $booking)
            <table border="1" style="width: 100%" cellpadding="5" nobr="true">
                <tr>
                    <th colspan="3"><strong>{{ $booking->user->printableName() }}

                        <?php

                        $contacts = [];

                        foreach($booking->user->contacts as $contact) {
                            if ($contact->type == 'phone' || $contact->type == 'mobile')
                                $contacts[] = $contact->value;
                        }

                        if (!empty($contacts))
                            echo ' - ' . join(', ', $contacts);

                    ?></strong></th>
                </tr>

                @include('documents.booking_shipping', [
                    'booking' => $booking,
                    'products_source' => 'products_with_friends'
                ])

                <tr>
                    <th colspan="3"><strong>{{ _i('Totale') }}: {{ printablePriceCurrency($booking->total_value, ',') }}</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
