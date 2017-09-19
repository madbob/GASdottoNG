<form class="form-horizontal main-form">
    <div class="col-md-4">
        @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
        @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
        @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
        @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
        @include('commons.orderstatus', ['order' => $order, 'editable' => false])
    </div>

    <div class="col-md-4">
    </div>

    @can('supplier.shippings', $order->supplier)
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
    @endcan
</form>
