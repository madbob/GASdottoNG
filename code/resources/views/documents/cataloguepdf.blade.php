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
        <h3>{{ _i('Listino %s del %s', [$supplier->name, date('d/m/Y')]) }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th width="40%"><strong>{{ _i('Nome') }}</strong></th>
                    <th width="20%"><strong>{{ _i('Unità di Misura') }}</strong></th>
                    <th width="20%"><strong>{{ _i('Prezzo Unitario') }}</strong></th>
                    <th width="20%"><strong>{{ _i('Trasporto') }}</strong></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    @if($product->variants->isEmpty())
                        <tr>
                            <td width="40%">{{ $product->name }}</td>
                            <td width="20%">{{ $product->measure->printableName() }}</td>
                            <td width="20%">{{ printablePriceCurrency($product->price, ',') }}</td>
                            <td width="20%">{{ printablePriceCurrency($product->transport, ',') }}</td>
                        </tr>
                    @else
                        @foreach($product->variantsCombinations() as $combination)
                            <tr>
                                <td width="40%">{{ $product->name }}<br><small>{{ $combination->name }}</small></td>
                                <td width="20%">{{ $product->measure->printableName() }}</td>
                                <td width="20%">{{ printablePriceCurrency($combination->price, ',') }}</td>
                                <td width="20%">{{ printablePriceCurrency($product->transport, ',') }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </body>
</html>
