<x-larastrap::modal :title="_i('Gestione Ordini Automatici')" size="fullscreen">
    <div class="row">
        <div class="col-md-12">
            {{ _i("Con questo strumento puoi gestire apertura e chiusura automatica degli ordini. Gli ordini che vengono aperti e chiusi insieme (dunque hanno gli stessi parametri di ricorrenza, chiusura e consegna) saranno automaticamente aggregati. Quando una ricorrenza è esaurita (tutte le sue occorrenza sono date passate) viene rimossa da questo elenco.") }}
        </div>
    </div>

    <hr>

    <x-larastrap::iform method="POST" :action="route('dates.updateorders')">
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
                        ],
                        [
                            'label' => _i('Tipo'),
                            'field' => 'type',
                            'type' => 'hidden',
                            'extra' => [
                                'value' => 'order'
                            ]
                        ],
                        [
                            'label' => _i('Fornitore'),
                            'field' => 'target_id',
                            'type' => 'selectobj',
                            'extra' => [
                                'options' => $currentuser->targetsByAction('supplier.orders')
                            ]
                        ],
                        [
                            'label' => _i('Ricorrenza'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                        ],
                        [
                            'label' => _i('Chiusura dopo...'),
                            'field' => 'end',
                            'type' => 'number',
                            'extra' => [
                                'textappend' => 'giorni'
                            ]
                        ],
                        [
                            'label' => _i('Consegna dopo...'),
                            'field' => 'shipping',
                            'type' => 'number',
                            'extra' => [
                                'textappend' => 'giorni',
								'attributes' => [
									'data-enforce-more' => '[name^=end]'
								]
                            ]
                        ],
                        [
                            'label' => _i('Commento'),
                            'field' => 'comment',
                            'type' => 'text',
                            'extra' => [
                                'max_length' => 40
                            ]
                        ],
                        [
                            'label' => _i('Sospendi'),
                            'field' => 'suspend',
                            'type' => 'check',
                            'help' => _i("Se un ordine automatico viene sospeso, le prossime aperture verranno ignorate. Usa questa opzione per gestire i periodi di inattività del GAS, ad esempio durante le festività."),
                            'extra' => [
                                'reviewCallback' => function($component, $params) {
                                    $params['hidden'] = $params['obj'] ? false : true;
                                    $params['value'] = $params['obj'] ? $params['obj']->id : 0;
                                    $params['checked'] = $params['obj'] && $params['obj']->suspend;
                                    return $params;
                                }
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
