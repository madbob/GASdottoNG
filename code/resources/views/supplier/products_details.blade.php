<div class="row">
    @php

    $sorting_rules = [];

    if ($currentgas->manual_products_sorting) {
        $sorting_rules['sorting'] = __('texts.generic.sortings.manual');
    }

    $sorting_rules['name'] = __('texts.generic.name');

    $sorting_rules['category_name'] = (object) [
        'label' => __('texts.generic.category'),
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
