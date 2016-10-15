<html>
        <body>
                <h3>Listino {{ $supplier->name }} del {{ date('d/m/Y') }}</h3>

                <hr/>

                <table border="1" style="width: 100%" cellpadding="5">
                        <thead>
                                <tr>
                                        <th width="40%"><strong>Nome</strong></th>
                                        <th width="20%"><strong>Unità di Misura</strong></th>
                                        <th width="20%"><strong>Prezzo Unitario</strong></th>
                                        <th width="20%"><strong>Trasporto</strong></th>
                                </tr>
                        </thead>
                        <tbody>
                                @foreach($supplier->products as $product)
                                <tr>
                                        <td width="40%">{{ $product->name }}</td>
                                        <td width="20%">{{ $product->measure->printableName() }}</td>
                                        <td width="20%">{{ printablePrice($product->price) }} €</td>
                                        <td width="20%">{{ printablePrice($product->transport) }} €</td>
                                </tr>
                                @endforeach
                        </tbody>
                </table>
        </body>
</html>
