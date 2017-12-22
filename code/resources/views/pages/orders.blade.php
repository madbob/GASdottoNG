@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('supplier.orders')
            @include('commons.addingbutton', [
                'template' => 'order.base-edit',
                'typename' => 'order',
                'typename_readable' => _i('Ordine'),
                'targeturl' => 'orders',
                'extra' => [
                    'post-saved-refetch' => '#aggregable-list'
                ]
            ])

            <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#orderSearch">{{ _i('Ricerca') }}</button>
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#orderAggregator">{{ _i('Aggrega Ordini') }}</button>

            <div class="modal fade" id="orderAggregator" tabindex="-1" role="dialog" aria-labelledby="orderAggregator">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal" method="POST" action="{{ url('aggregates') }}" data-toggle="validator">
                            <input type="hidden" name="update-select" value="category_id">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Aggrega Ordini') }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>
                                    {{ _i("Clicca e trascina gli ordini nella stessa cella per aggregarli, o nella cella vuota per disaggregarli.") }}
                                </p>
                                <p>
                                    {{ _i("Una volta aggregati, gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.") }}
                                </p>

                                <hr/>

                                <div id="aggregable-list" data-fetch-url="{{ url('aggregates/create') }}">
                                    @include('order.aggregable', ['orders' => $orders])
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="collapse list-filter" id="orderSearch" data-list-target="#wrapper-order-list">
                <div class="row">
                    <div class="col-md-6">
                        <div class="well">
                            <form class="form-horizontal" data-toggle="validator" method="GET" action="{{ url('orders/search') }}">
                                @include('commons.selectobjfield', [
                                    'obj' => null,
                                    'name' => 'supplier_id',
                                    'label' => _i('Fornitore'),
                                    'mandatory' => true,
                                    'objects' => App\Supplier::orderBy('name', 'asc')->get()
                                ])

                                @include('commons.genericdaterange')
                            </form>

                            <button class="btn btn-danger pull-right">{{ _i('Chiudi') }}</button>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
</div>

<div class="clearfix"></div>
<hr/>

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'order-list',
            'items' => $orders,
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>

@endsection
