@extends('app')

@section('content')

@can('supplier.orders')
    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'order.create',
                'typename' => 'order',
                'typename_readable' => _i('Ordine'),
                'targeturl' => 'orders',
            ])

            <x-larastrap::ambutton :label="_i('Aggrega Ordini')" :attributes="['data-modal-url' => route('aggregates.create')]" />
            <x-larastrap::ambutton :label="_i('Gestione Date')" :attributes="['data-modal-url' => route('dates.index')]" />
            <x-larastrap::ambutton :label="_i('Gestione Ordini Automatici')" :attributes="['data-modal-url' => route('dates.orders')]" />
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>

    <div class="row">
        <div class="col-12 col-md-6">
            <x-filler :data-action="url('orders/search')" data-fill-target="#main-order-list">
                @include('commons.genericdaterange', [
                    'start_date' => strtotime('-6 months'),
                    'end_date' => strtotime('+6 months'),
                ])

                <x-larastrap::selectobj name="supplier_id" :label="_i('Fornitore')" :options="$currentgas->suppliers" :extraitem="_i('Tutti')" />

                @php
                    $statuses = [
                        'open' => '<i class="bi-play"></i>',
                        'suspended' => '<i class="bi-pause"></i>',
                        'closed' => '<i class="bi-stop-fill"></i>',
                        'shipped' => '<i class="bi-skip-forward"></i>',
                        'archived' => '<i class="bi-eject"></i>',
                    ];
                @endphp
                <x-larastrap::checks name="status" :label="_i('Stato')" :options="$statuses" :value="['open', 'suspended', 'closed', 'shipped']" />
            </x-filler>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>
@endcan

<div class="row">
    <div class="col" id="main-order-list">
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
