@extends('app')

@section('content')

@can('supplier.orders')
    @if($has_old)
        <div class="row mb-4">
            <div class="col">
                <div class="alert alert-danger">
                    {{ __('texts.orders.help.unarchived_notice') }}
                </div>
            </div>
        </div>
     @endif

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'dynamic_url' => route('orders.create'),
                'typename' => 'order',
                'typename_readable' => __('texts.orders.name'),
            ])

            <x-larastrap::ambutton tlabel="orders.do_aggregate" :attributes="['data-modal-url' => route('aggregates.create')]" />
            <x-larastrap::ambutton tlabel="orders.admin_dates" :attributes="['data-modal-url' => route('dates.index')]" />
            <x-larastrap::ambutton tlabel="orders.admin_automatics" :attributes="['data-modal-url' => route('dates.orders')]" />
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

                <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentgas->suppliers" :extra_options="[0 => __('texts.generic.all')]" />

                @php

				$statuses = [];
				foreach(\App\Helpers\Status::orders() as $identifier => $meta) {
					$statuses[$identifier] = (object) [
						'label' => sprintf('<i class="bi-%s"></i>', $meta->icon),
						'attributes' => ['title' => $meta->label],
					];
				}

                @endphp

                <x-larastrap::checks name="status" tlabel="generic.status" :options="$statuses" :value="['open', 'suspended', 'closed', 'shipped']" />
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
                'supplier_name' => __('texts.orders.supplier'),
                'start' => __('texts.orders.dates.start'),
                'end' => __('texts.orders.dates.end'),
                'shipping' => __('texts.orders.dates.shipping'),
            ]
        ])
    </div>
</div>

@endsection
