@if(isset($editable) == false || $editable == true)
    @include('commons.selectenumfield', [
        'obj' => $order,
        'name' => 'status',
        'label' => 'Stato',
        'values' => [
            [
                'label' => 'Prenotazioni Aperte',
                'value' => 'open',
            ],
            [
                'label' => 'Prenotazioni Chiuse',
                'value' => 'closed',
            ],
            [
                'label' => 'Consegnato',
                'value' => 'shipped',
            ],
            [
                'label' => 'Archiviato',
                'value' => 'archived',
            ],
            [
                'label' => 'In Sospeso',
                'value' => 'suspended',
            ],
        ]
    ])
@else
    @include('commons.staticenumfield', [
        'obj' => $order,
        'name' => 'status',
        'label' => 'Stato',
        'values' => [
            [
                'label' => 'Prenotazioni Aperte',
                'value' => 'open',
            ],
            [
                'label' => 'Prenotazioni Chiuse',
                'value' => 'closed',
            ],
            [
                'label' => 'Consegnato',
                'value' => 'shipped',
            ],
            [
                'label' => 'Archiviato',
                'value' => 'archived',
            ],
            [
                'label' => 'In Sospeso',
                'value' => 'suspended',
            ],
        ]
    ])
@endif
