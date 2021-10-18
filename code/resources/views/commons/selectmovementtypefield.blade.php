<?php

if(!isset($show_all)) {
    $show_all = false;
}

$types = [
    'none' => _i('Seleziona un Tipo'),
];

foreach (movementTypes() as $info) {
    if ($show_all || $info->visibility) {
        $types[$info->id] = $info->name;
    }
}

?>

<x-larastrap::select name="type" :label="_i('Tipo')" :options="$types" classes="movement-type-selector" />
