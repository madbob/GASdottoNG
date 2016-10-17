<?php $summary = $order->calculateSummary(); ?>
<html>
        <body>
                <h3>Prodotti ordinati {{ $order->supplier->printableName() }} del {{ date('d/m/Y') }}</h3>

                <hr/>

                <table border="1" style="width: 100%" cellpadding="5">
                        <thead>
                                <tr>
                                        <th width="40%"><strong>Nome</strong></th>
                                        <th width="20%"><strong>Quantità Totale</strong></th>
                                        <th width="20%"><strong>Prezzo Totale</strong></th>
                                        <th width="20%"><strong>Trasporto</strong></th>
                                </tr>
                        </thead>
                        <tbody>
                                @foreach($order->supplier->products as $product)
                                <tr>
                                        <td width="40%">{{ $product->printableName() }}</td>
                                        <td width="20%">{{ $summary->products[$product->id]['quantity'] }}</td>
                                        <td width="20%">{{ printablePrice($summary->products[$product->id]['price']) }} €</td>
                                        <td width="20%">{{ printablePrice($summary->products[$product->id]['transport']) }} €</td>
                                </tr>
                                @endforeach
                        </tbody>
                </table>
        </body>
</html>
