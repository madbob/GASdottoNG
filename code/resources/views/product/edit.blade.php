@php

$buttons = [
    [
        'color' => 'light',
        'label' => __('generic.do_duplicate'),
        'classes' => ['float-start', 'me-2', 'async-modal'],
        'attributes' => [
            'data-modal-url' => route('products.duplicate', $product->id)
        ],
    ],
    [
        'color' => 'danger',
        'label' => __('generic.remove'),
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
