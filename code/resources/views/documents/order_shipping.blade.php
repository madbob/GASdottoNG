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
        <h3>{{ _i('Dettaglio Consegne Ordine %s presso %s del %s', [$order->internal_number, $order->supplier->printableName(), $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y')]) }}</h3>

        <br/>
        <hr>
        <br/>

        <?php

        $total = 0;
        $total_transport = 0;

        ?>

        @foreach($bookings as $booking)
            <table border="1" style="width: 100%" cellpadding="5" nobr="true">
                <tr>
                    <th colspan="3">
                        <?php

                        $head = '';

                        if($shipping_mode == 'all_by_place')
                            $head .= ($booking->user->shippingplace ? $booking->user->shippingplace->name : _i('[Luogo non Specificato]')) . ' - ';

                        $head .= $booking->user->printableName();

                        $discount = $booking->getValue('discount', true);
                        $booking_total = $booking->getValue('effective', true) - $discount;
                        $total += $booking_total;
                        $transport = $booking->getValue('transport', true);
                        $total_transport += $transport;

                        ?>
                        <strong>{{ $head }}</strong>
                    </th>
                </tr>

                @include('documents.booking_shipping', [
                    'booking' => $booking,
                    'with_friends' => true
                ])

                @if($transport != 0)
                    <tr>
                        <th colspan="3"><strong>{{ _i('Trasporto') }}: {{ printablePriceCurrency($transport, ',') }}</strong></th>
                    </tr>
                @endif

                @if($discount != 0)
                    <tr>
                        <th colspan="3"><strong>{{ _i('Sconto') }}: {{ printablePriceCurrency($discount, ',') }}</strong></th>
                    </tr>
                @endif

                <tr>
                    <th colspan="3"><strong>{{ _i('Totale') }}: {{ printablePriceCurrency($booking_total, ',') }}</strong></th>
                </tr>
            </table>

            <p>&nbsp;</p>
        @endforeach

        <table border="1" style="width: 100%" cellpadding="5" nobr="true">
            <tr>
                <th colspan="3"><strong>{{ _i('Totale') }}: {{ printablePriceCurrency($total, ',') }}</strong></th>
            </tr>

            @if(!empty($total_transport))
                <tr>
                    <th colspan="3"><strong>{{ _i('Trasporto') }}: {{ printablePriceCurrency($total_transport, ',') }}</strong></th>
                </tr>
            @endif
        </table>
    </body>
</html>
