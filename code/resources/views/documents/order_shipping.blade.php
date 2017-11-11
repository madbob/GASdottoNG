<html>
    <body>
        <h3>Dettaglio Consegne Ordine {{ $order->internal_number }} presso {{ $order->supplier->printableName() }} del {{ $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y') }}</h3>

        <hr/>

        @foreach($order->bookings as $booking)
            <table border="1" style="width: 100%" cellpadding="5">
                <thead>
                    <tr>
                        <th colspan="3">{{ $booking->user->printableName() }}

                            <?php

                            $contacts = [];

                            foreach($booking->user->contacts as $contact) {
                                if ($contact->type == 'phone' || $contact->type == 'mobile')
                                    $contacts[] = $contact->value;
                            }

                            if (!empty($contacts))
                                echo ' - ' . join(', ', $contacts);

                        ?></th>
                    </tr>
                </thead>
                <tbody>
                    @include('documents.booking_shipping', ['booking' => $booking])
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totale: {{ printablePrice($booking->total_value, ',') }} €</th>
                    </tr>
                </tfoot>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
