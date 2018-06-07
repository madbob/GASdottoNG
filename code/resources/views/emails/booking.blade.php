<p>
    {{ _i('Di seguito il riassunto dei prodotti che hai ordinato:') }}
</p>

@foreach($booking->bookings as $b)
    <?php $variable = false ?>
    <h3>{{ $b->order->supplier->printableName() }}</h3>

    <table style="width:100%">
        <thead>
            <th style="width:50%; text-align: left">{{ _i('Prodotto') }}</th>
            <th style="width:25%; text-align: left">{{ _i('Quantità') }}</th>
            <th style="width:25%; text-align: left">{{ _i('Prezzo') }}</th>
        </thead>

        <tbody>
            @foreach($b->products as $product)
                @if($product->quantity != 0)
                    <?php $variable = $variable || $product->product->variable ?>
                    <tr>
                        <td>{{ $product->product->printableName() }}</td>
                        <td>{{ $product->quantity }} {{ $product->product->printableMeasure() }}</td>
                        <td>{{ printablePriceCurrency($product->quantityValue()) }}</td>
                    </tr>
                @endif
            @endforeach

            <tr>
                <td><strong>{{ _i('Totale') }}</strong></td>
                <td>&nbsp;</td>
                <td>{{ printablePriceCurrency($b->value) }}</td>
            </tr>
        </tbody>
    </table>

    @if($b->friends_bookings->isEmpty() == false)
        <h5>{{ _i('Gli ordini dei tuoi amici') }}</h5>

        @foreach($b->friends_bookings as $fb)
            <p>{{ $fb->user->printableName() }}</p>

            <table style="width:100%">
                <thead>
                    <th style="width:50%; text-align: left">{{ _i('Prodotto') }}</th>
                    <th style="width:25%; text-align: left">{{ _i('Quantità') }}</th>
                    <th style="width:25%; text-align: left">{{ _i('Prezzo') }}</th>
                </thead>

                <tbody>
                    @foreach($fb->products as $product)
                        @if($product->quantity != 0)
                            <?php $variable = $variable || $product->product->variable ?>
                            <tr>
                                <td>{{ $product->product->printableName() }}</td>
                                <td>{{ $product->quantity }} {{ $product->product->printableMeasure() }}</td>
                                <td>{{ printablePriceCurrency($product->quantityValue()) }}</td>
                            </tr>
                        @endif
                    @endforeach

                    <tr>
                        <td><strong>{{ _i('Totale') }}</strong></td>
                        <td>&nbsp;</td>
                        <td>{{ printablePriceCurrency($fb->value) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endif

    @if($variable)
        <p>
            {{ _i("L'importo reale di questo ordine dipende dal peso effettivo dei prodotti consegnati; il totale qui riportato è solo indicativo.") }}
        </p>
    @endif
@endforeach

@if($b && $b->order->shipping != null)
    <p>
        {{ _i('La consegna avverrà %s.', $b->order->printableDate('shipping')) }}
    </p>
@endif

@if(!empty($txt_message))
    <hr/>
    <p>
        {!! nl2br($txt_message) !!}
    </p>
@endif
