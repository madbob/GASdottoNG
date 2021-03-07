<?php

$suppliers = $currentuser->targetsByAction('supplier.orders');

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">
        {{ _i('Gestione Date') }}
    </h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            {{ _i("Da qui è possibile aggiungere date arbitrarie al calendario delle consegne, anche per ordini non ancora esistenti. Questa funzione è consigliata per facilitare il coordinamento di diversi referenti all'interno del GAS e pianificare le attività a lungo termine.") }}
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-6">
            <div class="form-horizontal manyrows-dates-filter">
                @include('commons.selectobjfield', [
                    'obj' => null,
                    'name' => 'target_id',
                    'label' => _i('Fornitore'),
                    'objects' => $suppliers,
                    'extra_selection' => [
                        '0' => _i('Nessuno')
                    ]
                ])
                @include('commons.radios', [
                    'name' => 'type',
                    'label' => _i('Tipo'),
                    'values' => [
                        'all' => (object)['name' => _i('Tutti'), 'checked' => true],
                        'confirmed' => (object)['name' => _i('Confermato')],
                        'temp' => (object)['name' => _i('Provvisorio')]
                    ]
                ])

                <div class="form-group">
                    <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                        <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<form class="form-horizontal inner-form" method="PUT" action="{{ route('dates.update', 0) }}">
    <input type="hidden" name="close-modal" value="1">

    <div class="modal-body">
        <div class="row">
            <div class="col-md-12" id="dates-in-range">
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
                            'width' => 2,
                            'extra' => [
                                'objects' => $suppliers
                            ]
                        ],
                        [
                            'label' => _i('Data'),
                            'field' => 'date',
                            'type' => 'date',
                            'width' => 2,
                            'extra' => [
                                'defaults_now' => true
                            ]
                        ],
                        [
                            'label' => _i('Ricorrenza'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                            'width' => 3,
                        ],
                        [
                            'label' => _i('Descrizione'),
                            'field' => 'description',
                            'type' => 'text',
                            'width' => 2,
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
