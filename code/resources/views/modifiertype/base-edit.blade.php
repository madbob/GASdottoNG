@include('commons.textfield', [
    'obj' => $modtype,
    'name' => 'name',
    'label' => _i('Nome'),
    'disabled' => $modtype ? $modtype->system : false,
])

@include('commons.radios', [
    'obj' => $modtype,
    'name' => 'arithmetic',
    'label' => _i('Operazione'),
    'disabled' => $modtype ? $modtype->system : false,
    'values' => [
        'sum' => (object) [
            'name' => _i('Somma'),
            'checked' => true
        ],
        'sub' => (object) [
            'name' => _i('Sottrazione')
        ],
    ]
])

@include('commons.checkboxes', [
    'obj' => $modtype,
    'name' => 'classes',
    'label' => _i('Oggetti'),
    'values' => [
        'App\Product' => (object) [
            'name' => _i('Prodotti'),
        ],
        'App\Supplier' => (object) [
            'name' => _i('Fornitori/Ordini'),
        ],
        'App\Delivery' => (object) [
            'name' => _i('Luoghi di Consegna'),
        ],
    ]
])
