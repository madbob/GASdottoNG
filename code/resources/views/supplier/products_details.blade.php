<div class="row">
    <div class="col">
        @include('commons.loadablelist', [
            'identifier' => 'product-list-' . $supplier->id,
            'items' => $supplier->products()->with(['category'])->sorted()->get(),
            'legend' => (object)[
                'class' => App\Product::class
            ],
            'sorting_rules' => [
                'sorting' => _i('Ordinamento Manuale'),
                'name' => _i('Nome'),
                'category_name' => (object) [
                    'label' => _i('Categoria'),
                    'has_headers' => true,
                    'get_headers' => function($items) {
                        $categories = $items->pluck('category_id')->toArray();
                        $categories = array_unique($categories);
                        return App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();
                    }
                ],
            ]
        ])
    </div>
</div>
