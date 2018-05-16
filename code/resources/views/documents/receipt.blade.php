<html>
    <body>
        <p>
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
            <strong>Fattura {{ $receipt->number }} del {{ date('d/m/Y', strtotime($receipt->date)) }}</strong>
        </p>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <tbody>
                @php

                $rates = [];
                $grand_total = 0;

                @endphp

                @foreach($receipt->bookings as $booking)
                    @foreach($booking->products as $product)
                        @php
                        list($product_total, $product_total_tax) = $product->deliveredTaxedValue();

                        if (!isset($rates[$product->product->vat_rate_id]))
                            $rates[$product->product->vat_rate_id] = 0;

                        $rates[$product->product->vat_rate_id] += $product_total_tax;
                        $grand_total += $product_total + $product_total_tax;

                        @endphp

                        <tr>
                            <td>{{ $product->product->name }}</td>
                            <td>{{ printablePriceCurrency($product_total) }}</td>
                        </tr>
                    @endforeach
                @endforeach

                @foreach($rates as $id => $total)
                    <tr>
                        <td>{{ _i('IVA %s%%', App\VatRate::findOrFail($id)->percentage) }}</td>
                        <td>{{ printablePriceCurrency($total) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td><strong>{{ _i('Totale') }}</strong></td>
                    <td><strong>{{ printablePriceCurrency($grand_total) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
