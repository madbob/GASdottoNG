<?php $summary = $order->calculateSummary(); ?>
<html>
    <body>
        <h3>Prodotti Ordinati Ordine {{ $order->internal_number }} presso {{ $order->supplier->printableName() }} del {{ date('d/m/Y') }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th width="40%"><strong>Nome</strong></th>
                    <th width="15%"><strong>Quantità Totale</strong></th>
                    <th width="15%"><strong>Unità Misura</strong></th>
                    <th width="15%"><strong>Prezzo Totale</strong></th>
                    <th width="15%"><strong>Trasporto</strong></th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->supplier->products as $product)
                    @if($order->hasProduct($product))
                        @if(isset($summary->by_variant[$product->id]))
                            @foreach($summary->by_variant[$product->id] as $name => $variant)
                                @if($variant['quantity'] != 0)
                                    <tr>
                                        <td width="40%">{{ $product->printableName() }}<br/>{{ $name }}</td>
                                        <td width="15%">{{ printableQuantity($variant['quantity'], $product->measure->discrete, 2, ',') }}</td>
                                        <td width="15%">{{ $product->printableMeasure(true) }}</td>
                                        <td width="15%">{{ printablePrice($variant['price'], ',') }} €</td>
                                        <td width="15%">{{ printablePrice($summary->products[$product->id]['transport'], ',') }} €</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            @if($summary->products[$product->id]['quantity_pieces'] != 0)
                                <tr>
                                    <td width="40%">{{ $product->printableName() }}</td>
                                    <td width="15%">{{ printableQuantity($summary->products[$product->id]['quantity_pieces'], $product->measure->discrete, 2, ',') }}</td>
                                    <td width="15%">{{ $product->printableMeasure(true) }}</td>
                                    <td width="15%">{{ printablePrice($summary->products[$product->id]['price'], ',') }} €</td>
                                    <td width="15%">{{ printablePrice($summary->products[$product->id]['transport'], ',') }} €</td>
                                </tr>
                            @endif
                        @endif
                    @endif
                @endforeach
            </tbody>
        </table>
    </body>
</html>
