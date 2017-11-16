<html>
    <body>
        <h3>Dettaglio Consegne Ordine {{ $order->internal_number }} presso {{ $order->supplier->printableName() }} del {{ $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y') }}</h3>

        <hr/>

        @foreach($order->bookings as $booking)
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

                @include('documents.booking_shipping', ['booking' => $booking])

                <tr>
                    <th colspan="3"><strong>Totale: {{ printablePrice($booking->total_value, ',') }} €</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
