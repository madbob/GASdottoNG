<form class="form-horizontal main-form" method="PUT" action="{{ route('aggregates.update', $aggregate->id) }}">
    <div class="row">
        <div class="col-md-4">
            @include('commons.selectenumfield', [
                'obj' => null,
                'name' => 'status',
                'label' => _i('Stato'),
                'enforced_default' => 'no',
                'values' => [
                    [
                        'label' => _i('Invariato'),
                        'value' => 'no',
                    ],
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
                'help_popover' => _i("Da qui puoi modificare lo stato di tutti gli ordini inclusi nell'aggregato"),
            ])

            @include('commons.textfield', ['obj' => $aggregate, 'name' => 'comment', 'label' => _i('Commento')])

            @include('commons.boolfield', [
                'obj' => null,
                'name' => 'change_dates',
                'label' => _i('Modifica Date'),
                'extra_class' => 'collapse_trigger',
                'default_checked' => false,
                'help_popover' => _i("Da qui è possibile modificare la data di apertura, chiusura a consegna di tutti gli ordini inclusi nell'aggregato"),
            ])

            <div class="collapse" data-triggerable="change_dates">
                <div class="col-md-12">
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'start', 'label' => _i('Data Apertura')])
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'end', 'label' => _i('Data Chiusura')])
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'shipping', 'label' => _i('Data Consegna')])
                </div>
            </div>

            @if($currentgas->hasFeature('shipping_places'))
                @include('commons.selectobjfield', [
                    'obj' => $aggregate,
                    'name' => 'deliveries',
                    'label' => _i('Luoghi di Consegna'),
                    'mandatory' => false,
                    'objects' => $currentgas->deliveries,
                    'multiple_select' => true,
                    'extra_selection' => ['' => _i('Non limitare luogo di consegna')],
                    'help_text' => _i("Selezionando uno o più luoghi di consegna, l'ordine sarà visibile solo agli utenti che hanno attivato quei luoghi. Se nessun luogo viene selezionato, l'ordine sarà visibile a tutti. Tenere premuto Ctrl per selezionare più voci.")
                ])
            @endif
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => $currentgas->id])
        </div>
    </div>

    @include('commons.formbuttons', [
        'no_delete' => true
    ])
</form>
