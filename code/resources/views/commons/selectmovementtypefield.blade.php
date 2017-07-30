<?php

$types = [];

$types[] = [
    'label' => 'Seleziona un Tipo',
    'value' => 'none',
];

foreach (App\Movement::types() as $method_id => $info) {
    $types[] = [
        'label' => $info->name,
        'value' => $method_id,
    ];
}

?>

@include('commons.selectenumfield', [
    'obj' => null,
    'name' => 'type',
    'label' => 'Tipo',
    'values' => $types,
    'enforced_default' => 'none',
    'extra_class' => 'movement-type-selector'
])
