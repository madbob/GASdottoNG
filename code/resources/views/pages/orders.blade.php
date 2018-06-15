@extends('app')

@section('content')

@can('supplier.orders')
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'order.base-edit',
                'typename' => 'order',
                'typename_readable' => _i('Ordine'),
                'targeturl' => 'orders',
                'extra' => [
                    'post-saved-refetch' => '#aggregable-list'
                ]
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#orderAggregator">{{ _i('Aggrega Ordini') }}</button>

            <div class="modal fade" id="orderAggregator" tabindex="-1" role="dialog" aria-labelledby="orderAggregator">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal" method="POST" action="{{ route('aggregates.store') }}" data-toggle="validator">
                            <input type="hidden" name="update-select" value="category_id">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Aggrega Ordini') }}</h4>
                            </div>
                            <div class="modal-body">
                                @if(empty($orders))
                                    <p>
                                        {{ _i('Non ci sono elementi da visualizzare.') }}
                                    </p>
                                    <p>
                                        {{ _i("Una volta aggregati, gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.") }}
                                    </p>
                                @else
                                    <p>
                                        {{ _i("Clicca e trascina gli ordini nella stessa cella per aggregarli, o in una cella vuota per disaggregarli.") }}
                                    </p>

                                    <hr/>

                                    <div id="aggregable-list" data-fetch-url="{{ route('aggregates.create') }}">
                                        @include('order.aggregable', ['orders' => $orders])
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>

    <div class="row">
        <div class="col-md-6">
            <div class="form-horizontal form-filler" data-action="{{ url('orders/search') }}" data-toggle="validator" data-fill-target="#main-order-list">
                @include('commons.genericdaterange', [
                    'start_date' => strtotime('-1 years'),
                    'end_date' => strtotime('+1 years'),
                ])
                @include('commons.selectobjfield', [
                    'obj' => null,
                    'name' => 'supplier_id',
                    'label' => _i('Fornitore'),
                    'objects' => $currentgas->suppliers,
                    'extra_selection' => [
                        '0' => _i('Tutti')
                    ]
                ])
                @include('commons.checkboxes', [
                    'name' => 'status',
                    'label' => _i('Stato'),
                    'values' => [
                        'open' => (object) [
                            'icon' => 'play',
                            'checked' => true
                        ],
                        'suspended' => (object) [
                            'icon' => 'pause',
                            'checked' => true
                        ],
                        'closed' => (object) [
                            'icon' => 'stop',
                            'checked' => true
                        ],
                        'shipped' => (object) [
                            'icon' => 'step-forward',
                            'checked' => true
                        ],
                        'archived' => (object) [
                            'icon' => 'eject',
                            'checked' => false
                        ],
                    ]
                ])

                <div class="form-group">
                    <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                        <button type="submit" class="btn btn-success">{{ _i('Ricerca') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>
@endcan

<div class="row">
    <div class="col-md-12" id="main-order-list">
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
