<?php

if(!isset($show_all))
    $show_all = false;

$types = [];

$types[] = [
    'label' => 'Seleziona un Tipo',
    'value' => 'none',
];

foreach (App\MovementType::types() as $info) {
    if ($show_all || $info->visibility) {
        $types[] = [
            'label' => $info->name,
            'value' => $info->id,
        ];
    }
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
