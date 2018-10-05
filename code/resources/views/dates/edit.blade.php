<form class="form-horizontal" method="PUT" action="{{ route('dates.update', 0) }}">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
            {{ _i('Gestione Date') }}
        </h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                @include('commons.manyrows', [
                    'contents' => $dates,
                    'show_columns' => true,
                    'columns' => [
                        [
                            'label' => _i('ID'),
                            'field' => 'id',
                            'type' => 'hidden',
                            'width' => 0
                        ],
                        [
                            'label' => _i('Fornitore'),
                            'field' => 'target_id',
                            'type' => 'selectobj',
                            'width' => 3,
                            'extra' => [
                                'objects' => App\Supplier::orderBy('name', 'asc')->get()
                            ]
                        ],
                        [
                            'label' => _i('Data'),
                            'field' => 'date',
                            'type' => 'date',
                            'width' => 2,
                        ],
                        [
                            'label' => _i('Descrizione'),
                            'field' => 'description',
                            'type' => 'text',
                            'width' => 3,
                        ],
                        [
                            'label' => _i('Tipo'),
                            'field' => 'type',
                            'type' => 'selectenum',
                            'width' => 2,
                            'extra' => [
                                'values' => App\Date::types()
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
        <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
    </div>
</form>
