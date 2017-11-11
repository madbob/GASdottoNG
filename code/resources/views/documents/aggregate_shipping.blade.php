<html>
    <body>
        <h3>Dettaglio Consegne Ordini<br/>
            @foreach($aggregate->orders as $order)
                {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
            @endforeach
        </h3>

        @foreach($aggregate->bookings as $super_booking)
            @if($super_booking->total_value == 0)
                @continue
            @endif

            <table border="1" style="width: 100%" cellpadding="5">
                <thead>
                    <tr>
                        <th colspan="3">{{ $super_booking->user->printableName() }}

                            <?php

                            $contacts = [];

                            foreach($super_booking->user->contacts as $contact) {
                                if ($contact->type == 'phone' || $contact->type == 'mobile')
                                    $contacts[] = $contact->value;
                            }

                            if (!empty($contacts))
                                echo ' - ' . join(', ', $contacts);

                        ?></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($super_booking->bookings as $booking)
                        @if($booking->products->isEmpty() == false)
                            <tr>
                                <td colspan="3"><strong>{{ $booking->order->supplier->printableName() }}</strong></td>
                            </tr>

                            @include('documents.booking_shipping', ['booking' => $booking])
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totale: {{ printablePrice($super_booking->total_value, ',') }} €</th>
                    </tr>
                </tfoot>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
