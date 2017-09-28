<?php $summary = $order->calculateSummary() ?>

<form class="form-horizontal main-form order-editor" method="PUT" action="{{ url('orders/' . $order->id) }}">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />

    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
            @include('commons.staticstringfield', ['obj' => $order, 'name' => 'internal_number', 'label' => 'Numero'])

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textfield', ['obj' => $order, 'name' => 'comment', 'label' => 'Commento'])
                @include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'end',
                    'label' => 'Data Chiusura',
                    'mandatory' => true,
                    'extras' => [
                        'data-enforce-after' => '.date[name=start]'
                    ]
                ])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'shipping',
                    'label' => 'Data Consegna',
                    'extras' => [
                        'data-enforce-after' => '.date[name=end]'
                    ]
                ])
            @else
                @include('commons.staticstringfield', ['obj' => $order, 'name' => 'comment', 'label' => 'Commento'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-md-4">
            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textfield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale', 'postlabel' => '€ / %'])
                @include('commons.decimalfield', ['obj' => $order, 'name' => 'transport', 'label' => 'Spese Trasporto', 'is_price' => true])
            @else
                @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale'])
                @include('commons.staticpricefield', ['obj' => $order, 'name' => 'transport', 'label' => 'Spese Trasporto'])
            @endif

            @include('commons.movementfield', [
                'obj' => $order->payment,
                'name' => 'payment_id',
                'label' => 'Pagamento',
                'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered),
                'to_modal' => [
                    'amount_editable' => true
                ]
            ])
        </div>
        <div class="col-md-4">
            <div class="well pull-right">
                <h4>Files</h4>

                <div class="list-group pull-right">
                    @foreach($order->attachments as $attachment)
                        <a href="{{ $attachment->download_url }}" class="list-group-item">{{ $attachment->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <hr/>

    @include('order.summary', ['order' => $order, 'summary' => $summary])
    @include('commons.formbuttons', ['export_url' => $order->exportableURL()])
</form>
