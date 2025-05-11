@extends('app')

@section('content')

@can('supplier.orders')
    @if($has_old)
        <div class="row mb-4">
            <div class="col">
                <div class="alert alert-danger">
                    {{ _i('Ci sono ordini chiusi da oltre un anno ma non archiviati: cercali usando la funzione di ricerca qui sotto. È raccomandato archiviare i vecchi ordini, in modo che non siano più visualizzati nella dashboard ed il caricamento delle pagine sia più veloce. Gli ordini archiviati possono comunque essere sempre recuperati con la funzione di ricerca.') }}
                </div>
            </div>
        </div>
     @endif

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'dynamic_url' => route('orders.create'),
                'typename' => 'order',
                'typename_readable' => _i('Ordine'),
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

                <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentgas->suppliers" :extra_options="[0 => _i('Tutti')]" />

                @php

				$statuses = [];
				foreach(\App\Helpers\Status::orders() as $identifier => $meta) {
					$statuses[$identifier] = (object) [
						'label' => sprintf('<i class="bi-%s"></i>', $meta->icon),
						'attributes' => ['title' => $meta->label],
					];
				}

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
                'class' => App\Aggregate::class
            ],
            'sorting_rules' => [
                'supplier_name' => __('orders.supplier'),
                'start' => _i('Data Apertura'),
                'end' => _i('Data Chiusura'),
                'shipping' => _i('Data Consegna'),
            ]
        ])
    </div>
</div>

@endsection
