<?php

$display_shipping_date = true;

switch($booking->status) {
    case 'shipped':
        $intro_text = _i('Di seguito il riassunto dei prodotti che ti sono stati consegnati:');
        $display_shipping_date = false;
        $attribute = 'delivered';
        $function = 'deliveredValue';
        break;

    case 'saved':
        $intro_text = _i('Di seguito il riassunto dei prodotti che ti saranno consegnati:');
        $attribute = 'delivered';
        $function = 'deliveredValue';
        break;

    case 'pending':
        $intro_text = _i('Di seguito il riassunto dei prodotti che hai ordinato:');
        $attribute = 'quantity';
        $function = 'quantityValue';
        break;
}

$global_total = 0;
$bookings_tot = 0;

?>

@if(!empty($txt_message))
    <p>
        {!! nl2br($txt_message) !!}
    </p>

    <hr/>
@endif

<p>
    {{ $intro_text }}
</p>

@foreach($booking->bookings as $b)
    <?php $variable = false ?>

    <h3>{{ $b->order->supplier->printableName() }}</h3>

    <table style="width:100%">
        <thead>
            <th style="width:50%; text-align: left">{{ _i('Prodotto') }}</th>
            <th style="width:25%; text-align: left">{{ _i('Quantità') }}</th>
            <th style="width:25%; text-align: left">{{ _i('Prezzo') }}</th>
        </thead>

        <tbody>
            @foreach($b->products as $product)
                @if($product->$attribute != 0)
                    <?php $variable = $variable || $product->product->variable ?>
                    <tr>
                        <td>{{ $product->product->printableName() }}</td>
                        <td>{{ $product->$attribute }} {{ $product->product->printableMeasure() }}</td>
                        <td>{{ printablePriceCurrency($product->$function()) }}</td>
                    </tr>
                @endif
            @endforeach

            <?php

            $bookings_tot++;
            $tot = $b->getValue('effective', false);
            $global_total += $tot;

            ?>

            <tr>
                <td><strong>{{ _i('Trasporto') }}</strong></td>
                <td>&nbsp;</td>
                <td>{{ printablePriceCurrency($b->getValue('transport', false)) }}</td>
            </tr>
            <tr>
                <td><strong>{{ _i('Sconto') }}</strong></td>
                <td>&nbsp;</td>
                <td>{{ printablePriceCurrency($b->getValue('discount', false)) }}</td>
            </tr>
            <tr>
                <td><strong>{{ _i('Totale') }}</strong></td>
                <td>&nbsp;</td>
                <td>{{ printablePriceCurrency($tot) }}</td>
            </tr>
        </tbody>
    </table>

    @if($b->friends_bookings->isEmpty() == false)
        <h5>{{ _i('Gli ordini dei tuoi amici') }}</h5>

        @foreach($b->friends_bookings as $fb)
            <p>{{ $fb->user->printableName() }}</p>

            <table style="width:100%">
                <thead>
                    <th style="width:50%; text-align: left">{{ _i('Prodotto') }}</th>
                    <th style="width:25%; text-align: left">{{ _i('Quantità') }}</th>
                    <th style="width:25%; text-align: left">{{ _i('Prezzo') }}</th>
                </thead>

                <tbody>
                    @foreach($fb->products as $product)
                        @if($product->$attribute != 0)
                            <?php $variable = $variable || $product->product->variable ?>
                            <tr>
                                <td>{{ $product->product->printableName() }}</td>
                                <td>{{ $product->$attribute }} {{ $product->product->printableMeasure() }}</td>
                                <td>{{ printablePriceCurrency($product->$function()) }}</td>
                            </tr>
                        @endif
                    @endforeach

                    <?php

                    $bookings_tot++;
                    $tot = $fb->getValue('effective', false);
                    $global_total += $tot;

                    ?>

                    <tr>
                        <td><strong>{{ _i('Trasporto') }}</strong></td>
                        <td>&nbsp;</td>
                        <td>{{ printablePriceCurrency($fb->getValue('transport', false)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _i('Sconto') }}</strong></td>
                        <td>&nbsp;</td>
                        <td>{{ printablePriceCurrency($fb->getValue('discount', false)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _i('Totale') }}</strong></td>
                        <td>&nbsp;</td>
                        <td>{{ printablePriceCurrency($tot) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endif

    <br>

    @if($display_shipping_date && $variable)
        <p>
            {{ _i("L'importo reale di questo ordine dipende dal peso effettivo dei prodotti consegnati; il totale qui riportato è solo indicativo.") }}
        </p>
    @endif

    <p>
        {{ _i("Per comunicazioni su quest'ordine, si raccomanda di contattare:") }}
    </p>
    <ul>
        @foreach($b->order->enforcedContacts() as $contact)
            <li>{{ $contact->printableName() }} - {{ $contact->email }}</li>
        @endforeach
    </ul>
@endforeach

@if($bookings_tot > 1)
    <p>
        {{ _i('Totale da pagare: %s', [printablePriceCurrency($global_total)]) }}
    </p>
@endif

@if($display_shipping_date && $b && $b->order->shipping != null)
    <p>
        {{ _i('La consegna avverrà %s.', [$b->order->printableDate('shipping')]) }}
    </p>
@endif
