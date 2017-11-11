<form class="form-horizontal main-form">
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
            @include('commons.orderstatus', ['order' => $order, 'editable' => false])
        </div>

        <div class="col-md-4">
            @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale'])
            @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'transport', 'label' => 'Spese Trasporto'])
        </div>

        <div class="col-md-4">
            @can('supplier.shippings', $order->supplier)
                @include('order.files', ['order' => $order])
            @endcan
        </div>
    </div>

    <hr/>

    @include('order.summary_ro', ['order' => $order, 'summary' => $order->calculateSummary()])
</form>
