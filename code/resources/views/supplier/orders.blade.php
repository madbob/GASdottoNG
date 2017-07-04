<div class="row">
    <div class="col-md-12">
        <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#orderSearch-{{ $supplier->id }}">Ricerca</button>

        <div class="collapse list-filter" id="orderSearch-{{ $supplier->id }}" data-list-target="#wrapper-order-list-{{ $supplier->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="well">
                        <form class="form-horizontal" data-toggle="validator" method="GET" action="{{ url('orders/search') }}">
                            @include('commons.hiddenfield', ['prefix' => 'supplier_', 'name' => 'id', 'obj' => $supplier, 'extra_class' => 'enforce_filter'])
                            @include('commons.hiddenfield', ['name' => 'list_identifier', 'value' => 'order-list-' . $supplier->id, 'extra_class' => 'enforce_filter'])
                            @include('commons.genericdaterange')
                        </form>

                        <button class="btn btn-danger pull-right">Chiudi</button>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>

    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'order-list-' . $supplier->id,
            'items' => $supplier->aggregates->take(10)->get(),
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>
