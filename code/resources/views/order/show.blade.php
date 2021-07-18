<?php $summary = $order->reduxData() ?>

<x-larastrap::mform :obj="$order" nosave nodelete>
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])
            <x-larastrap::datepicker name="start" :label="_i('Data Apertura')" readonly disabled />
            <x-larastrap::datepicker name="end" :label="_i('Data Chiusura')" readonly disabled />
            <x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" readonly disabled />

            @if($order->deliveries()->count() != 0)
                @include('commons.staticobjectslistfield', ['obj' => $order, 'name' => 'deliveries', 'label' => _i('Luoghi di Consegna')])
            @endif

            @include('commons.orderstatus', ['order' => $order, 'editable' => false])
        </div>

        <div class="col-md-4">
            @include('commons.staticmodifications', ['obj' => $order])

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('supplier.movements', $order->supplier))
                @include('commons.movementfield', [
                    'obj' => $order->payment,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                    'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered),
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

    @include('order.summary_ro', ['order' => $order, 'summary' => $summary])

    @can('supplier.shippings', $order->supplier)
        @include('order.annotations', ['order' => $order])
    @endcan
</x-larastrap::mform>
