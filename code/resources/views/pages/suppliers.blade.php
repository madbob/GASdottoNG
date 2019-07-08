@extends('app')

@section('content')

@if(Gate::check('supplier.add', $currentgas) || Gate::check('categories.admin', $currentgas) || Gate::check('measures.admin', $currentgas))
    <div class="row">
        <div class="col-md-12">
            @can('supplier.add', $currentgas)
                @include('commons.addingbutton', [
                    'template' => 'supplier.base-edit',
                    'typename' => 'supplier',
                    'typename_readable' => _i('Fornitore'),
                    'targeturl' => 'suppliers'
                ])
            @endcan

            @can('categories.admin', $currentgas)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleCategories">{{ _i('Amministra Categorie') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
                <div class="modal fade dynamic-contents close-on-submit" id="handleCategories" tabindex="-1" role="dialog" aria-labelledby="handleCategories" data-contents-url="{{ route('categories.index') }}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        </div>
                    </div>
                </div>
            @endcan

            @can('measures.admin', $currentgas)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleMeasures">{{ _i('Amministra Unità di Misura') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
                <div class="modal fade dynamic-contents close-on-submit" id="handleMeasures" tabindex="-1" role="dialog" data-contents-url="{{ route('measures.index') }}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        </div>
                    </div>
                </div>

                <div class="modal fade dynamic-contents upper-modal" id="showMeasureProducts" tabindex="-1" role="dialog" data-contents-url="">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>
@endif

<div class="row">
    <div class="col-md-12">
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
