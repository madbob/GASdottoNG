@php

$buttons = [
    [
        'color' => 'light',
        'label' => _i('Duplica'),
        'classes' => ['float-start', 'async-modal'],
        'attributes' => [
            'data-modal-url' => route('products.duplicate', $product->id)
        ],
    ],
    [
        'color' => 'danger',
        'label' => _i('Elimina'),
        'classes' => ['async-modal'],
        'attributes' => [
            'data-modal-url' => route('products.askdelete', $product->id)
        ],
    ]
];

@endphp

<x-larastrap::mform :obj="$product" classes="product-editor" method="PUT" :action="route('products.update', $product->id)" :other_buttons="$buttons" nodelete="true">
    @include('product.editform', ['product' => $product])
    <hr>
</x-larastrap::mform>
