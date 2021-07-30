@extends('app')

@section('content')

@if(Gate::check('supplier.add', $currentgas) || Gate::check('categories.admin', $currentgas) || Gate::check('measures.admin', $currentgas))
    <div class="row">
        <div class="col">
            @can('supplier.add', $currentgas)
                @include('commons.addingbutton', [
                    'template' => 'supplier.base-edit',
                    'typename' => 'supplier',
                    'typename_readable' => _i('Fornitore'),
                    'targeturl' => 'suppliers'
                ])
            @endcan

            @can('categories.admin', $currentgas)
                <x-larastrap::ambutton id="category_admin" :label="_i('Amministra Categorie')" :data-modal-url="route('categories.index')" />
            @endcan

            @can('measures.admin', $currentgas)
                <x-larastrap::ambutton id="unit_measure_admin" :label="_i('Amministra Unità di Misura')" :data-modal-url="route('measures.index')" />
            @endcan

            @if($currentgas->getConfig('es_integration'))
                <x-larastrap::ambutton :label="_i('Indice Remoto')" :data-modal-url="route('import.esmodal')" />
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
                    'class' => 'Supplier'
                ],
                'filters' => [
                    'deleted_at' => (object)[
                        'icon' => 'inbox',
                        'label' => _i('Cessati'),
                        'value' => null
                    ]
                ]
            ])
        @else
            @include('commons.loadablelist', [
                'identifier' => 'supplier-list',
                'items' => $suppliers,
                'legend' => (object)[
                    'class' => 'Supplier'
                ],
            ])
        @endif
    </div>
</div>

@endsection
