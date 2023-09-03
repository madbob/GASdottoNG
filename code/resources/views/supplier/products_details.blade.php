<div class="row">
    @php

    $sorting_rules = [];

    if ($currentgas->manual_products_sorting) {
        $sorting_rules['sorting'] = _i('Ordinamento Manuale');
    }

    $sorting_rules['name'] = _i('Nome');

    $sorting_rules['category_name'] = (object) [
        'label' => _i('Categoria'),
        'has_headers' => true,
        'get_headers' => function($items) {
            $categories = $items->pluck('category_id')->toArray();
            $categories = array_unique($categories);
            return App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();
        }
    ];

    @endphp

    <div class="col">
        @include('commons.loadablelist', [
            'identifier' => 'product-list-' . $supplier->id,
            'items' => $supplier->products()->with(['category'])->sorted()->get(),
            'legend' => (object)[
                'class' => App\Product::class
            ],
            'sorting_rules' => $sorting_rules,
        ])
    </div>
</div>
