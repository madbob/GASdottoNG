<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'order-list-' . $supplier->id,
            'items' => $supplier->aggregates->take(10)->get(),
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>
