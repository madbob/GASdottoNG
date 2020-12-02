<?php $suppliers = $currentuser->targetsByAction('supplier.orders') ?>

@include('commons.selectobjfield', [
    'obj' => $order,
    'name' => 'supplier_id',
    'label' => _i('Fornitore'),
    'mandatory' => true,
    'objects' => $suppliers
])

@include('commons.textfield', [
    'obj' => $order,
    'name' => 'comment',
    'label' => _i('Commento')
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
    ]
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
        @include('dates.list', ['dates' => array_values($suppliers)[0]->dates])
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
        'help_text' => _i("Tenere premuto Ctrl per selezionare più luoghi di consegna. Se nessun luogo viene selezionato, l'ordine sarà visibile a tutti.")
    ])
@endif

@include('commons.orderstatus', ['order' => $order])
