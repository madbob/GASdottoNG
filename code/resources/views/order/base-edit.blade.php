<?php

$user = Auth::user();
$suppliers = $user->relatedObjectsByPermission('supplier.orders')

?>

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

@include('commons.orderstatus', ['order' => $order])
