<html>
    <body>
        <p>
            @if(localFilePath($receipt->user->gas, 'logo') != null)
                <img src="{{ localFilePath($receipt->user->gas, 'logo') }}" style="width: 150px"><br>
            @endif

            <?php $gas_data = $receipt->user->gas->extra_invoicing ?>
            {{ $gas_data['business_name'] }}<br>
            @if(!empty($gas_data['address']))
                {{ $gas_data['address'] }}<br>
            @endif
            @if(!empty($gas_data['taxcode']))
                {{ _i('C.F. %s', $gas_data['taxcode']) }}<br>
            @endif
            @if(!empty($gas_data['vat']))
                {{ _i('P.IVA %s', $gas_data['vat']) }}
            @endif
        </p>

        <p style="text-align: right">
            {{ $receipt->user->printableName() }}<br>
            {{ join(', ', $receipt->user->getAddress()) }}<br>
            {{ $receipt->user->taxcode }}
        </p>

        <p>
            &nbsp;
        </p>

        <p>
            <strong>Ricevuta Fiscale {{ $receipt->number }} del {{ date('d/m/Y', strtotime($receipt->date)) }}</strong>
        </p>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th width="55%"><strong>{{ _i('Prodotto') }}</strong></th>
                    <th width="15%"><strong>{{ _i('Quantità') }}</strong></th>
                    <th width="15%"><strong>{{ _i('Unità Misura') }}</strong></th>
                    <th width="15%"><strong>{{ _i('Prezzo') }}</strong></th>
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
                            <td width="55%">{{ $product->product->name }}</td>
                            <td width="15%">{{ $product->true_delivered }}</td>
                            <td width="15%">{{ $product->product->measure->printableName() }}</td>
                            <td width="15%">{{ printablePriceCurrency($product_total) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <p>
            &nbsp;
        </p>

        <table border="0" style="width: 100%" cellpadding="5">
            <tbody>
                @foreach($rates as $id => $total)
                    <tr>
                        <td width="85%">{{ _i('IVA %s%%', App\VatRate::findOrFail($id)->percentage) }}</td>
                        <td width="15%">{{ printablePriceCurrency($total) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td width="85%"><strong>{{ _i('Totale') }}</strong></td>
                    <td width="15%"><strong>{{ printablePriceCurrency($grand_total) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
