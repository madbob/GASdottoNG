@extends('app')

@section('content')

@if(Gate::check('supplier.add', $currentgas) || Gate::check('categories.admin', $currentgas) || Gate::check('measures.admin', $currentgas))
    <div class="row">
        <div class="col">
            @can('supplier.add', $currentgas)
                @include('commons.addingbutton', [
                    'template' => 'supplier.base-edit',
                    'typename' => 'supplier',
                    'typename_readable' => __('texts.orders.supplier'),
                    'targeturl' => 'suppliers'
                ])
            @endcan

            @can('categories.admin', $currentgas)
                <x-larastrap::ambutton id="category_admin" tlabel="supplier.admin_categories" :data-modal-url="route('categories.index')" />
            @endcan

            @can('measures.admin', $currentgas)
                <x-larastrap::ambutton id="unit_measure_admin" tlabel="supplier.admin_measures" :data-modal-url="route('measures.index')" />
            @endcan

            @if($currentgas->getConfig('es_integration'))
                <x-larastrap::ambutton tlabel="supplier.remote_index" :data-modal-url="route('import.esmodal')" />
            @endif
        </div>
    </div>

    <hr/>
@endif

<div class="row">
    <div class="col">
        @can('supplier.add', $currentgas)
            @include('commons.loadablelist', [
                'identifier' => 'supplier-list',
                'items' => $suppliers,
                'legend' => (object)[
                    'class' => App\Supplier::class
                ],
                'filters' => [
                    'deleted_at' => (object)[
                        'icon' => 'inbox',
                        'label' => __('texts.user.all_ceased'),
                        'value' => null
                    ]
                ]
            ])
        @else
            @include('commons.loadablelist', [
                'identifier' => 'supplier-list',
                'items' => $suppliers,
                'legend' => (object)[
                    'class' => App\Supplier::class
                ],
            ])
        @endif
    </div>
</div>

@endsection
