@extends('app')

@section('content')

@can('supplier.orders')
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'order.create',
                'typename' => 'order',
                'typename_readable' => _i('Ordine'),
                'targeturl' => 'orders',
                'extra_size' => true,
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#orderAggregator">{{ _i('Aggrega Ordini') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
            <div class="modal fade dynamic-contents" id="orderAggregator" tabindex="-1" role="dialog" data-contents-url="{{ route('aggregates.create') }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#orderDates">{{ _i('Gestione Date') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
            <div class="modal fade dynamic-contents" id="orderDates" tabindex="-1" role="dialog" data-contents-url="{{ route('dates.index') }}">
                <div class="modal-dialog modal-extra-lg" role="document">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>

    <div class="row">
        <div class="col-md-12 col-lg-6">
            <div class="form-horizontal form-filler" data-action="{{ url('orders/search') }}" data-toggle="validator" data-fill-target="#main-order-list">
                @include('commons.genericdaterange', [
                    'start_date' => strtotime('-6 months'),
                    'end_date' => strtotime('+6 months'),
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
                    <div class="col-sm-{{ $fieldsize }} col-sm-offset-{{ $labelsize }}">
                        <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
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
            'sorting_rules' => [
                'supplier_name' => _i('Fornitore'),
                'start' => _i('Data Apertura'),
                'end' => _i('Data Chiusura'),
                'shipping' => _i('Data Consegna'),
            ]
        ])
    </div>
</div>

@endsection
