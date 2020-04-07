<?php $summary = $order->calculateSummary() ?>

<form class="form-horizontal main-form">
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => _i('Data Apertura'), 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => _i('Data Chiusura'), 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => _i('Data Consegna')])
            @include('commons.orderstatus', ['order' => $order, 'editable' => false])
        </div>

        <div class="col-md-4">
            @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'discount', 'label' => _i('Sconto Globale')])
            @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'transport', 'label' => _i('Spese Trasporto')])

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('supplier.movements', $order->supplier))
                @include('commons.movementfield', [
                    'obj' => $order->payment,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                    'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered + $summary->transport_delivered),
                    'to_modal' => [
                        'amount_editable' => true
                    ]
                ])
            @endif
        </div>

        <div class="col-md-4">
            @can('supplier.shippings', $order->supplier)
                @include('order.files', ['order' => $order])
            @endcan
        </div>
    </div>

    <hr/>

    <?php $summary = $order->calculateSummary() ?>
    @include('order.summary_ro', ['order' => $order, 'summary' => $summary])

    @can('supplier.shippings', $order->supplier)
        @include('order.annotations', ['order' => $order, 'summary' => $summary])
    @endcan

    @include('commons.formbuttons', [
        'no_delete' => true,
        'no_save' => true,
    ])
</form>
