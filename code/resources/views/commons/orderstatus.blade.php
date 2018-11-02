@if(isset($editable) == false || $editable == true)
    @include('commons.selectenumfield', [
        'obj' => $order,
        'name' => 'status',
        'label' => _i('Stato'),
        'values' => [
            [
                'label' => _i('Prenotazioni Aperte'),
                'value' => 'open',
            ],
            [
                'label' => _i('Prenotazioni Chiuse'),
                'value' => 'closed',
            ],
            [
                'label' => _i('Consegnato'),
                'value' => 'shipped',
            ],
            [
                'label' => _i('Archiviato'),
                'value' => 'archived',
            ],
            [
                'label' => _i('In Sospeso'),
                'value' => 'suspended',
            ],
        ],
        'help_text' => $order == null ? _i("All'apertura dell'ordine, saranno inviate mail di annuncio a tutti gli utenti che hanno abilitato le notifiche per il fornitore selezionato.") : ''
    ])
@else
    @include('commons.staticenumfield', [
        'obj' => $order,
        'name' => 'status',
        'label' => _i('Stato'),
        'values' => [
            [
                'label' => _i('Prenotazioni Aperte'),
                'value' => 'open',
            ],
            [
                'label' => _i('Prenotazioni Chiuse'),
                'value' => 'closed',
            ],
            [
                'label' => _i('Consegnato'),
                'value' => 'shipped',
            ],
            [
                'label' => _i('Archiviato'),
                'value' => 'archived',
            ],
            [
                'label' => _i('In Sospeso'),
                'value' => 'suspended',
            ],
        ]
    ])
@endif
