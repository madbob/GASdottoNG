<?php

if (!isset($show_all)) {
    $show_all = false;
}

if (!isset($field_name)) {
    $field_name = 'type';
}

if (!isset($current_label)) {
    $current_label = __('texts.generic.type');
}

if (!isset($current_pophelp)) {
    $current_pophelp = '';
}

if (!isset($empty_label)) {
    $empty_label = __('texts.generic.select');
}

$types = [
    'none' => $empty_label,
];

foreach (movementTypes() as $info) {
    if ($show_all || $info->visibility) {
        $types[$info->id] = $info->name;
    }
}

?>

<x-larastrap::select :name="$field_name" :label="$current_label" :options="$types" classes="movement-type-selector" :pophelp="$current_pophelp" />
