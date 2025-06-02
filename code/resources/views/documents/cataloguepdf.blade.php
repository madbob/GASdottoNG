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
        <h3>{{ __('supplier.products_list_heading', [$supplier->name, date('d/m/Y')]) }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    @foreach($headers as $head)
                        <th scope="col">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $product)
                    <tr>
                        @foreach($product as $p)
                            <td>{{ $p }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
