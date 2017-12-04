<?php $cellsize = round(100 / count($data->headers), 3) ?>
<html>
    <body>
        <h3>{{ _i('Prodotti ordinati ordine %s presso del %s', $order->internal_number, $order->supplier->printableName(), $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y')) }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    @foreach($data->headers as $header)
                        <th width="{{ $cellsize }}%"><strong>{{ $header }}</strong></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data->contents as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
