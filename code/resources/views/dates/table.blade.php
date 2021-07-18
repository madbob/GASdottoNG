<?php $suppliers = $currentuser->targetsByAction('supplier.orders') ?>

<x-larastrap::modal :title="_i('Gestione Date')">
    <div class="row">
        <div class="col-md-12">
            {{ _i("Da qui è possibile aggiungere date arbitrarie al calendario delle consegne, anche per ordini non ancora esistenti. Questa funzione è consigliata per facilitare il coordinamento di diversi referenti all'interno del GAS e pianificare le attività a lungo termine.") }}
        </div>
    </div>

    <hr>

    <x-larastrap::form method="PUT" :action="route('dates.update', 0)">
        <input type="hidden" name="close-modal" value="1">

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
                                'options' => $suppliers
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
                            'type' => 'select',
                            'width' => 2,
                            'extra' => [
                                'options' => App\Date::types()
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
