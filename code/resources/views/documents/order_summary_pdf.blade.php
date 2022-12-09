<?php $cellsize = round(100 / count($blocks[0]->headers), 3) ?>
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
        @if(isset($order))
            <h3>{{ _i('Prodotti ordine %s presso %s del %s', [$order->internal_number, $order->supplier->printableName(), $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y')]) }}</h3>
        @else
            <h3>
                {{ _i('Prodotti') }}<br/>
                @if($aggregate->orders()->count() <= aggregatesConvenienceLimit())
                    @foreach($aggregate->orders as $order)
                        {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
                    @endforeach
                @endif
            </h3>
        @endif

        @foreach($blocks as $data)
            <hr/>

            @if(count($blocks) > 1)
                <h4>{{ $data->title }}</h4>
            @endif

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
        @endforeach
    </body>
</html>
