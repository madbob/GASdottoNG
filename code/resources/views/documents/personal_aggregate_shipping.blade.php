<!DOCTYPE html>
<html lang="it">
    <head>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }

            table th, table td {
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <h3>{{ __('texts.orders.files.order.shipping') }}<br/>
            @if($aggregate->orders()->count() <= aggregatesConvenienceLimit())
                @foreach($aggregate->orders as $order)
                    {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
                @endforeach
            @endif
        </h3>

        @php
        $valid_bookings = 0;
        $super_total_booked = 0;
        $super_total_delivered = 0;
        $allAggregatedModifiers = [];
        @endphp

        @foreach($bookings as $super_booking)
            @if($super_booking->getValue('booked', false) == 0 && $super_booking->getValue('delivered', false) == 0)
                @continue
            @endif

            <?php $booked_cell_value = $delivered_cell_value = 0 ?>

            <table border="1" style="width: 100%" nobr="true">
                <thead>
                    <tr>
                        <th scope="col" colspan="5">
                            <strong>{{ $super_booking->user->printableName() }}</strong>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($super_booking->bookings as $booking)
                        @if($booking->products->isEmpty() == false)
                            @php

                            $booked = $booking->getValue('booked', false, true);
                            $delivered = $booking->getValue('delivered', false, true);
                            $booked_cell_value += $booked;
                            $delivered_cell_value += $delivered;
                            $super_total_booked += $booked;
                            $super_total_delivered += $delivered;

                            $valid_bookings++;

                            @endphp

                            <tr>
                                <td colspan="5"><strong>{{ $booking->order->supplier->printableName() }}</strong></td>
                            </tr>

                            <tr>
                                <td width="40%"><strong>{{ __('texts.products.name') }}</strong></td>
                                <td width="15%"><strong>{{ __('texts.orders.booking.statuses.booked') }}</strong></td>
                                <td width="15%">&nbsp;</td>
                                <td width="15%"><strong>{{ __('texts.orders.booking.statuses.shipped') }}</strong></td>
                                <td width="15%">&nbsp;</td>
                            </tr>

                            @foreach($booking->products as $product)
                                @if($product->variants->isEmpty() == false)
                                    @foreach($product->variants as $variant)
                                        @if(!empty($variant->quantity) || !empty($variant->delivered))
                                            <tr>
                                                <td>{{ $product->product->printableName() }}</td>
                                                <td>{{ printableQuantity($variant->quantity, $product->product->measure->discrete, 2, ',') }} {{ $variant->printableName() }}</td>
                                                <td>{{ printablePriceCurrency($variant->quantityValue(), ',') }}</td>
                                                <td>{{ printableQuantity($variant->delivered, $product->product->measure->discrete, 2, ',') }} {{ $variant->printableName() }}</td>
                                                <td>{{ printablePriceCurrency($variant->deliveredValue(), ',') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    @if(!empty($product->quantity) || !empty($product->delivered))
                                        <tr>
                                            <td>{{ $product->product->printableName() }}</td>
                                            <td>{{ printableQuantity($product->quantity, $product->product->measure->discrete, 2, ',') }}</td>
                                            <td>{{ printablePriceCurrency($product->getValue('booked'), ',') }}</td>
                                            <td>{{ printableQuantity($product->delivered, $product->product->measure->discrete, 2, ',') }}</td>
                                            <td>{{ printablePriceCurrency($product->getValue('delivered'), ',') }}</td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if(!empty($booking->notes))
                        <tr>
                            <td colspan="5">{!! nl2br($booking->notes) !!}</td>
                        </tr>
                    @endif

                    @php
                    $booking_modifiers = $booking->aggregatedModifiers();
                    $allAggregatedModifiers = App\ModifiedValue::mergeAggregated($allAggregatedModifiers, $booking_modifiers);
                    @endphp

                    @foreach($booking_modifiers as $am)
                        <tr>
                            <th scope="row" colspan="5"><strong>{{ $am->name }}: {{ App\ModifiedValue::printAggregated($am) }}</th>
                        </tr>
                    @endforeach

                    <tr>
                        <th scope="row" colspan="5"><strong>{{ __('texts.orders.totals.booked') }}: {{ printablePriceCurrency($booked_cell_value, ',') }}</strong></th>
                    </tr>
                    <tr>
                        <th scope="row" colspan="5"><strong>{{ __('texts.orders.totals.shipped') }}: {{ printablePriceCurrency($delivered_cell_value, ',') }}</strong></th>
                    </tr>
                </tbody>
            </table>

            <p>&nbsp;</p>
        @endforeach

        @if($valid_bookings > 1)
            <table border="1" style="width: 100%" nobr="true">
                <thead>
                    <tr>
                        <th scope="col" colspan="5">
                            <strong>{{ __('texts.orders.totals.total') }}</strong>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($allAggregatedModifiers as $am)
                        <tr>
                            <th scope="row" colspan="5"><strong>{{ $am->name }}: {{ App\ModifiedValue::printAggregated($am) }}</th>
                        </tr>
                    @endforeach

                    <tr>
                        <th scope="row" colspan="5"><strong>{{ __('texts.orders.totals.booked') }}: {{ printablePriceCurrency($super_total_booked, ',') }}</strong></th>
                    </tr>
                    <tr>
                        <th scope="row" colspan="5"><strong>{{ __('texts.orders.totals.shipped') }}: {{ printablePriceCurrency($super_total_delivered, ',') }}</strong></th>
                    </tr>
                </tbody>
            </table>
        @endif
    </body>
</html>
