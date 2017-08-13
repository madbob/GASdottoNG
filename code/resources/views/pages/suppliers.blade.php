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

@can('categories.admin', $currentgas)
    <div class="modal fade" id="createCategory" tabindex="-1" role="dialog" aria-labelledby="createCategory">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="form-horizontal creating-form" method="POST" action="{{ url('categories') }}" data-toggle="validator">
                    <input type="hidden" name="update-select" value="category_id">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Crea Nuova Categoria</h4>
                    </div>
                    <div class="modal-body">
                        @include('commons.selectobjfield', [
                            'obj' => null,
                            'name' => 'parent_id',
                            'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
                            'extra_selection' => ['null' => 'Nessuna'],
                            'label' => 'Categoria Padre'
                        ])

                        @include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('measures.admin', $currentgas)
    <div class="modal fade" id="createMeasure" tabindex="-1" role="dialog" aria-labelledby="createMeasure">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="form-horizontal creating-form" method="POST" action="{{ url('measures') }}" data-toggle="validator">
                    <input type="hidden" name="update-select" value="measure_id">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Crea Nuova Unità di Misura</h4>
                    </div>
                    <div class="modal-body">
                        @include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
                        @include('commons.boolfield', ['obj' => null, 'name' => 'discrete', 'label' => 'Unità Discreta'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@endsection
