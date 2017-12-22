<?php

$classes = modelsUsingTrait('App\CreditableTrait');
$target_classes = [];

$target_classes[] = [
    'value' => null,
    'label' => _i('Nessuno'),
];

foreach($classes as $class => $name) {
    $target_classes[] = [
        'value' => $class,
        'label' => $name,
    ];
}

?>

@include('commons.textfield', ['obj' => $movementtype, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
@include('commons.boolfield', ['obj' => $movementtype, 'name' => 'allow_negative', 'label' => _i('Accetta Valori Negativi')])
@include('commons.decimalfield', ['obj' => $movementtype, 'name' => 'fixed_value', 'label' => _i('Valore Fisso'), 'is_price' => true])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'sender_type',
    'label' => _i('Pagante'),
    'values' => $target_classes
])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'target_type',
    'label' => _i('Pagato'),
    'values' => $target_classes
])
