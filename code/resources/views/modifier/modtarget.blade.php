@include('commons.radios', [
    'obj' => $modifier,
    'name' => 'applies_target',
    'label' => _i('Riferimento su cui applicare il modificatore'),
    'values' => $applies_targets,
])

<div class="distribution_type_selection {{ $modifier->applies_target != 'order' ? 'hidden' : '' }}">
    @include('commons.radios', [
        'obj' => $modifier,
        'name' => 'distribution_type',
        'label' => _i('Distribuzione sulle prenotazioni in base a'),
        'values' => [
            'none' => (object) [
                'name' => _i('Nessuno'),
                'hidden' => true,
            ],
            'quantity' => (object) [
                'name' => _i('Quantità'),
            ],
            'price' => (object) [
                'name' => _i('Valore'),
            ],
            'weight' => (object) [
                'name' => _i('Peso'),
            ],
        ]
    ])
</div>
