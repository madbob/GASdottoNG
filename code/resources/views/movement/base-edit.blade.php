@include('commons.selectmovementtypefield')

<div class="selectors">
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
    'label' => 'Identificativo'
])

@include('commons.textarea', [
    'obj' => null,
    'name' => 'notes',
    'label' => 'Note'
])
