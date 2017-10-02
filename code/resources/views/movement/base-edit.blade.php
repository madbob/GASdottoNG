@include('commons.selectmovementtypefield')

@include('commons.datefield', [
    'obj' => null,
    'name' => 'date',
    'label' => 'Data',
    'defaults_now' => true
])

<div class="selectors" data-empty-on-modal="true">
</div>
