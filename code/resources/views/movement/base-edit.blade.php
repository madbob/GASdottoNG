<?php

$types = [];

$types[] = [
	'label' => 'Seleziona un Tipo',
	'value' => 'none',
];

foreach(App\Movement::types() as $method_id => $info)
	$types[] = [
		'label' => $info->name,
		'value' => $method_id,
	];

?>

@include('commons.selectenumfield', [
	'obj' => null,
	'name' => 'type',
	'label' => 'Tipo',
	'values' => $types,
	'extra_class' => 'movement-type-selector'
])

<div class="selectors">
</div>

@include('commons.textfield', [
	'obj' => null,
	'name' => 'identifier',
	'label' => 'Identificativo'
])

@include('commons.textarea', [
	'obj' => null,
	'name' => 'notes',
	'label' => 'Note'
])
