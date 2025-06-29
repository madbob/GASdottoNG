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
        <p>
            @if(localFilePath($receipt->user->gas, 'logo') != null)
                <img src="{{ localFilePath($receipt->user->gas, 'logo') }}" style="width: 150px" alt="Logo"><br>
            @endif

            <?php $gas_data = $receipt->user->gas->extra_invoicing ?>
            {{ trim($gas_data['business_name']) }}<br>
            @if(!empty($gas_data['address']))
                {{ trim($gas_data['address']) }}<br>
            @endif
            @if(!empty($gas_data['taxcode']))
                {{ __('texts.user.taxcode') }} {{ trim($gas_data['taxcode']) }}<br>
            @endif
            @if(!empty($gas_data['vat']))
                {{ __('texts.supplier.vat') }} {{ trim($gas_data['vat']) }}<br>
            @endif
        </p>

        <p style="text-align: right">
            {{ trim($receipt->user->printableName()) }}<br>
            {{ trim(join(', ', $receipt->user->getAddress())) }}<br>
            {{ trim($receipt->user->taxcode) }}
        </p>

        <p>
            &nbsp;
        </p>

        <p>
            <strong>Ricevuta Fiscale {{ $receipt->number }} del {{ date('d/m/Y', strtotime($receipt->date)) }}</strong>
        </p>

        <hr/>

        <table border="1" style="width: 100%">
            <thead>
                <tr>
                    <th scope="col" width="55%"><strong>{{ __('texts.products.name') }}</strong></th>
                    <th scope="col" width="15%"><strong>{{ __('texts.generic.quantity') }}</strong></th>
                    <th scope="col" width="15%"><strong>{{ __('texts.generic.measure') }}</strong></th>
                    <th scope="col" width="15%"><strong>{{ __('texts.generic.price') }}</strong></th>
                </tr>
            </thead>
            <tbody>
                @php

                $rates = [];
                $grand_total = 0;

                @endphp

                @foreach($receipt->bookings as $booking)
                    @foreach($booking->products as $product)
                        @php

                        list($product_total, $product_total_tax) = $product->deliveredTaxedValue();

                        if ($product->product->vat_rate_id != null) {
                            if (!isset($rates[$product->product->vat_rate_id]))
                                $rates[$product->product->vat_rate_id] = 0;

                            $rates[$product->product->vat_rate_id] += $product_total_tax;
                        }

                        $grand_total += $product_total + $product_total_tax;

                        @endphp

                        <tr>
                            <td>{{ $product->product->name }}</td>
                            <td>{{ $product->delivered }}</td>
                            <td>{{ $product->product->measure->printableName() }}</td>
                            <td>{{ printablePriceCurrency($product_total) }}</td>
                        </tr>
                    @endforeach

                    @foreach($booking->aggregatedModifiers() as $am)
                        @php

                        $grand_total += $am->amount;

                        @endphp

                        <tr>
                            <td>{{ $am->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>{{ App\ModifiedValue::printAggregated($am) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <p>
            &nbsp;
        </p>

        <table border="0" style="width: 100%">
            <tbody>
                @foreach($rates as $id => $total)
                    <tr>
                        <td width="85%">{{ __('texts.generic.vat') }} {{ App\VatRate::findOrFail($id)->percentage }}%</td>
                        <td width="15%">{{ printablePriceCurrency($total) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td width="85%"><strong>{{ __('texts.orders.totals.total') }}</strong></td>
                    <td width="15%"><strong>{{ printablePriceCurrency($grand_total) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
