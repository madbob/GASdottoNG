<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'product-list-' . $supplier->id,
            'items' => $supplier->all_products,
            'legend' => (object)[
                'class' => 'Product'
            ]
        ])
    </div>
</div>
