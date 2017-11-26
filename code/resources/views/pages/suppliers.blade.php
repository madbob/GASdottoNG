@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('supplier.add', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'supplier.base-edit',
                'typename' => 'supplier',
                'typename_readable' => 'Fornitore',
                'targeturl' => 'suppliers'
            ])
        @endcan

        @can('categories.admin', $currentgas)
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleCategories">Amministra Categorie</button>
            <div class="modal fade dynamic-contents close-on-submit" id="handleCategories" tabindex="-1" role="dialog" aria-labelledby="handleCategories" data-contents-url="{{ url('categories') }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
        @endcan

        @can('measures.admin', $currentgas)
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleMeasures">Amministra Unità di Misura</button>
            <div class="modal fade dynamic-contents close-on-submit" id="handleMeasures" tabindex="-1" role="dialog" data-contents-url="{{ url('measures') }}">
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
                        'label' => 'Eliminati',
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
