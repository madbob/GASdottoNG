<p>
    Di seguito il riassunto dei prodotti che hai ordinato:
</p>

@foreach($booking->bookings as $b)
    <?php $variable = false ?>
    <h3>{{ $b->order->supplier->printableName() }}</h3>

    <table>
        <thead>
            <th>Prodotto</th>
            <th>Quantità</th>
            <th>Prezzo</th>
        </thead>

        <tbody>
            @foreach($b->products as $product)
                <?php $variable = $variable || $product->product->variable ?>
                <tr>
                    <td>{{ $product->product->printableName() }}</td>
                    <td>{{ $product->quantity }} {{ $product->product->printableMeasure() }}</td>
                    <td>{{ printablePrice($product->quantityValue()) }} €</td>
                </tr>
            @endforeach

            <tr>
                <td><strong>Totale</strong></td>
                <td>&nbsp;</td>
                <td>{{ printablePrice($b->value) }} €</td>
            </tr>
        </tbody>
    </table>

    @if($variable)
        <p>
            L'importo reale di questo ordine dipende dal peso effettivo dei prodotti consegnati; il totale qui riportato è solo indicativo.
        </p>
    @endif
@endforeach
