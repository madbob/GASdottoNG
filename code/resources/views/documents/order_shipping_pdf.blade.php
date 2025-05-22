<?php

/*
    Problema sulla formattazione del documento: c'è chi lo preferisce senza
    interruzioni di pagina in mezzo alle tabelle, e chi lo vuole continuo (per
    evitare tanti spazi vuoti nelle pagine stampate).
    Qui scelgo in base alla dimensione delle prenotazioni: se ce ne sono di
    grandi (più di 15 prodotti) opto per la visualizzazione continua, altrimenti
    preferisco quella che predilige l'accorpamento delle tabelle nelle pagine
*/
$preferred_style = 'breakup';
foreach($data->contents as $d) {
    if (count($d->products) >= 20) {
        $preferred_style = 'compact';
        break;
    }
}

?>

<html>
    <head>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }

            .main-wrapper {
                display: table;
                width: 100%;
            }

            .row {
                display: table-row;
                width: 100%;
            }

            .cell {
                border-top: 1px solid #000;
                border-left: 1px solid #000;
                display: table-cell;
                padding: 4px;
            }

            .cell.last-row {
                border-bottom: 1px solid #000;
            }

            .cell.last-in-row {
                border-right: 1px solid #000;
            }

            .extended {
                font-weight: bold;
                text-align: center;
                display: block;
                padding: 4px 0;
                width: 100%;
            }

            ul {
                margin: 0;
            }

            li {
                margin: 0;
            }

            h3, h4 {
                width: 100%;
                background-color: #555;
                color: #FFF;
                text-align: center;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        @if(isset($order))
            <h3>{{ _i('Dettaglio Consegne Ordine %s a %s del %s', [$order->internal_number, $order->supplier->printableName(), $order->shipping ? date('d/m/Y', strtotime($order->shipping)) : date('d/m/Y')]) }}</h3>
        @else
            <h3>
                {{ _i('Dettaglio Consegne del %s', [$aggregate->shipping ? date('d/m/Y', strtotime($aggregate->shipping)) : date('d/m/Y')]) }}<br/>
                @if($aggregate->orders()->count() <= aggregatesConvenienceLimit())
                    @foreach($aggregate->orders as $order)
                        {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
                    @endforeach
                @endif
            </h3>
        @endif

        <br/><hr><br/>

        <div class="extended">
            {!! join('<br>', $fields->user_columns_names) !!}
        </div>

        <div class="main-wrapper">
            <div class="row">
                @foreach($fields->product_columns_names as $h)
                    <div class="cell last-row {{ $loop->last ? 'last-in-row' : '' }}">{{ $h }}</div>
                @endforeach
            </div>
        </div>

        <br/><hr><br/>

        <?php

        $total = 0;
        $full_modifiers = [];
        $previous_shipping = null;

        ?>

        @foreach($data->contents as $d)
            @if($circles->sortedByUser() === false && $previous_shipping != $d->circles_sorting)
                <h4>{{ $d->circles_sorting }}</h4>
                <?php $previous_shipping = $d->circles_sorting ?>
            @endif

            @if($preferred_style == 'breakup')
                <table style="width: 100%">
                    <tr>
                        <td>
            @endif

            <div class="extended">
                {!! join('<br>', array_filter($d->user)) !!}

                <?php

                $booking_total = 0;
                $booking_modifiers = [];

                foreach($d->totals as $key => $value) {
                    if ($key == 'total') {
                        $booking_total += $value;
                        $total += $value;
                    }
                    else {
                        $booking_modifiers[$key] = $booking_modifiers[$key] ?? 0;
                        $booking_modifiers[$key] += $value;
                        $full_modifiers[$key] = $full_modifiers[$key] ?? 0;
                        $full_modifiers[$key] += $value;
                    }
                }

                ?>
            </div>

            <div class="main-wrapper">
                @foreach($d->products as $product)
                    <div class="row">
                        @foreach($product as $p)
                            @php

                            $extraclass = [];

                            if ($loop->last) {
                                $extraclass[] = 'last-in-row';
                            }

                            if ($loop->parent->last) {
                                $extraclass[] = 'last-row';
                            }

                            @endphp
                            <div class="cell {{ join(' ', $extraclass) }}">{{ $p }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            @if(!empty($d->notes))
                <div class="extended">{!! join('<br>', $d->notes) !!}</div>
            @endif

            @foreach($booking_modifiers as $bm_key => $bm_value)
                <div class="extended"><strong>{{ $bm_key }}: {{ printablePriceCurrency($bm_value, ',') }}</strong></div>
            @endforeach

            <div class="extended"><strong>{{ __('orders.totals.total') }}: {{ printablePriceCurrency($booking_total, ',') }}</strong></div>

            @if($preferred_style == 'breakup')
                        </td>
                    </tr>
                </table>
            @endif

            <p>&nbsp;</p>
        @endforeach

        @foreach($full_modifiers as $fm_key => $fm_value)
            <div class="extended"><strong>{{ $fm_key }}: {{ printablePriceCurrency($fm_value, ',') }}</strong></div>
        @endforeach

        <div class="extended"><strong>{{ __('orders.totals.total') }}: {{ printablePriceCurrency($total, ',') }}</strong></div>
    </body>
</html>
