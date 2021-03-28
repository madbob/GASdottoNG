<?php

$suppliers = $currentuser->targetsByAction('supplier.orders');
usort($suppliers, function($a, $b) {
    return $a->name <=> $b->name;
});

?>

@if($currentuser->gas->hasFeature('auto_aggregates'))
    <div class="form-group">
        <label for="contacts" class="col-sm-{{ $labelsize }} control-label">
            @include('commons.helpbutton', ['help_popover' => _i("Selezionando diversi fornitori, verranno generati i rispettivi ordini e saranno automaticamente aggregati. Questa funzione viene attivata se nel database sono presenti almeno 3 aggregati con almeno %d ordini ciascuno.", App\Aggregate::aggregatesConvenienceLimit())])
            {{ _i('Fornitori') }}
        </label>

        <div class="col-sm-{{ $fieldsize }}">
            @include('commons.manyrows', [
                'contents' => $order ? collect([$order]) : new Illuminate\Support\Collection(),
                'new_label' => _i('Aggiungi'),
                'columns' => [
                    [
                        'label' => _i('Fornitore'),
                        'field' => 'supplier_id',
                        'type' => 'selectobj',
                        'width' => 11,
                        'extra' => [
                            'objects' => $suppliers
                        ]
                    ],
                ]
            ])
        </div>
    </div>
@else
    @include('commons.selectobjfield', [
        'obj' => $order,
        'name' => 'supplier_id',
        'label' => _i('Fornitore'),
        'mandatory' => true,
        'objects' => $suppliers
    ])
@endif

@include('commons.textarea', [
    'obj' => $order,
    'name' => 'comment',
    'label' => _i('Commento'),
    'rows' => 2,
    'help_popover' => _i("Eventuale testo informativo da visualizzare nel titolo dell'ordine. Se più lungo di %d caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.", [App\Order::longCommentLimit()]),
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'start',
    'label' => _i('Data Apertura Prenotazioni'),
    'defaults_now' => true,
    'mandatory' => true
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'end',
    'label' => _i('Data Chiusura Prenotazioni'),
    'defaults_now' => true,
    'mandatory' => true,
    'extras' => [
        'data-enforce-after' => '.date[name=start]'
    ],
    'help_popover' => _i("Data di chiusura dell'ordine. Al termine del giorno qui indicato, l'ordine sarà automaticamente impostato nello stato \"Prenotazioni Chiuse\""),
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'shipping',
    'label' => _i('Data Consegna'),
    'defaults_now' => true,
    'extras' => [
        'data-enforce-after' => '.date[name=end]'
    ]
])

@if(empty($suppliers) == false)
    <div class="supplier-future-dates">
        @include('dates.list', ['dates' => array_values($suppliers)[0]->calendarDates])
    </div>
@endif

@if($currentgas->hasFeature('shipping_places'))
    @include('commons.selectobjfield', [
        'obj' => $order,
        'name' => 'deliveries',
        'label' => _i('Luoghi di Consegna'),
        'mandatory' => false,
        'objects' => $currentgas->deliveries,
        'multiple_select' => true,
        'extra_selection' => ['' => _i('Non limitare luogo di consegna')],
        'help_popover' => _i("Selezionando uno o più luoghi di consegna, l'ordine sarà visibile solo agli utenti che hanno attivato quei luoghi. Se nessun luogo viene selezionato, l'ordine sarà visibile a tutti. Tenere premuto Ctrl per selezionare più voci.")
    ])
@endif

@include('commons.orderstatus', ['order' => $order])
