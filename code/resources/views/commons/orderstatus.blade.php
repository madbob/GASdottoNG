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
        'help_popover' => _i("Stato attuale dell'ordine. Può assumere i valori:<ul><li>prenotazioni aperte: tutti gli utenti vedono l'ordine e possono sottoporre le loro prenotazioni. Quando l'ordine viene impostato in questo stato vengono anche inviate le email di annuncio</li><li>prenotazioni chiuse: tutti gli utenti vedono l'ordine ma non possono aggiungere o modificare le prenotazioni. Gli utenti abilitati possono comunque intervenire</li><li>consegnato: l'ordine appare nell'elenco degli ordini solo per gli utenti abilitati, ma nessun valore può essere modificato né tantomeno possono essere modificate le prenotazioni</li><li>archiviato: l'ordine non appare più nell'elenco, ma può essere ripescato con la funzione di ricerca</li><li>in sospeso: l'ordine appare nell'elenco degli ordini solo per gli utenti abilitati, e può essere modificato</li></ul>")
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
