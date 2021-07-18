<x-larastrap::modal :title="_i('Gestione Ordini Automatici')">
    <div class="row">
        <div class="col-md-12">
            {{ _i("Con questo strumento puoi gestire apertura e chiusura automatica degli ordini. Gli ordini che vengono aperti e chiusi insieme (dunque hanno gli stessi parametri di ricorrenza, chiusura e consegna) saranno automaticamente aggregati. Quando una ricorrenza è esaurita (tutte le sue occorrenza sono date passate) viene rimossa da questo elenco.") }}
        </div>
    </div>

    <hr>

    <x-larastrap::form method="POST" :action="route('dates.updateorders')">
        <input type="hidden" name="reload-whole-page" value="1">

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
                            'label' => _i('Tipo'),
                            'field' => 'type',
                            'type' => 'hidden',
                            'width' => 0,
                            'extra' => [
                                'value' => 'order'
                            ]
                        ],
                        [
                            'label' => _i('Fornitore'),
                            'field' => 'target_id',
                            'type' => 'selectobj',
                            'width' => 2,
                            'extra' => [
                                'options' => $currentuser->targetsByAction('supplier.orders')
                            ]
                        ],
                        [
                            'label' => _i('Ricorrenza'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                            'width' => 2,
                        ],
                        [
                            'label' => _i('Chiusura dopo...'),
                            'field' => 'end',
                            'type' => 'number',
                            'width' => 2,
                            'extra' => [
                                'textappend' => 'giorni'
                            ]
                        ],
                        [
                            'label' => _i('Consegna dopo...'),
                            'field' => 'shipping',
                            'type' => 'number',
                            'width' => 2,
                            'extra' => [
                                'textappend' => 'giorni'
                            ]
                        ],
                        [
                            'label' => _i('Commento'),
                            'field' => 'comment',
                            'type' => 'text',
                            'width' => 2,
                            'extra' => [
                                'max_length' => 40
                            ]
                        ],
                        [
                            'label' => _i('Sospendi'),
                            'field' => 'suspend',
                            'type' => 'check',
                            'width' => 1,
                            'help' => _i("Se un ordine automatico viene sospeso, le prossime aperture verranno ignorate. Usa questa opzione per gestire i periodi di inattività del GAS, ad esempio durante le festività."),
                            'extra' => [
                                'valuefrom' => 'id'
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
