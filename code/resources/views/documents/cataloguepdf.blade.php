<html>
    <body>
        <h3>{{ _i('Listino %s del %s', $supplier->name, date('d/m/Y')) }}</h3>

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
                @foreach($supplier->products as $product)
                <tr>
                    <td width="40%">{{ $product->name }}</td>
                    <td width="20%">{{ $product->measure->printableName() }}</td>
                    <td width="20%">{{ printablePrice($product->price, ',') }} {{ $currentgas->currency }}</td>
                    <td width="20%">{{ printablePrice($product->transport, ',') }} {{ $currentgas->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
