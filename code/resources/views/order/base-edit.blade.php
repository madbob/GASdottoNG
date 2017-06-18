<?php

$user = Auth::user();
$suppliers = [];

foreach ($user->roles as $role) {
    if ($role->enabledAction('supplier.orders'))
        foreach($role->applications() as $app)
            if (get_class($app) == 'App\Supplier')
                $suppliers[$app->id] = $app;
}

?>

@include('commons.selectobjfield', [
    'obj' => $order,
    'name' => 'supplier_id',
    'label' => 'Fornitore',
    'mandatory' => true,
    'objects' => $suppliers
])

@include('commons.textfield', [
    'obj' => $order,
    'name' => 'comment',
    'label' => 'Commento'
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'start',
    'label' => 'Data Apertura Prenotazioni',
    'defaults_now' => true,
    'mandatory' => true
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'end',
    'label' => 'Data Chiusura Prenotazioni',
    'mandatory' => true,
    'extras' => [
        'data-enforce-after' => '.date[name=start]'
    ]
])

@include('commons.datefield', [
    'obj' => $order,
    'name' => 'shipping',
    'label' => 'Data Consegna',
    'extras' => [
        'data-enforce-after' => '.date[name=end]'
    ]
])

@include('commons.orderstatus', ['order' => $order])
