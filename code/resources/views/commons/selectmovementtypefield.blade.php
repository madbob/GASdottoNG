<?php

$types = [];

$types[] = [
    'label' => 'Seleziona un Tipo',
    'value' => 'none',
];

foreach (App\MovementType::types() as $info) {
    if ($info->visibility) {
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
