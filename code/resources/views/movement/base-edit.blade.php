@include('commons.selectmovementtypefield')

<div class="selectors" data-empty-on-modal="true">
</div>

@include('commons.datefield', [
    'obj' => null,
    'name' => 'date',
    'label' => 'Data',
    'defaults_now' => true
])

@include('commons.textfield', [
    'obj' => null,
    'name' => 'identifier',
    'label' => 'Identificativo',
    'enforced_default' => ''
])

@include('commons.textarea', [
    'obj' => null,
    'name' => 'notes',
    'label' => 'Note',
    'enforced_default' => ''
])
