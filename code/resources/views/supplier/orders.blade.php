<div class="row">
    <div class="col-12 col-md-6">
        <x-filler :data-action="url('orders/search')" :data-fill-target="sprintf('#wrap-order-list-%s', $supplier->id)">
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 months'),
                'end_date' => strtotime('+1 months'),
            ])
        </x-filler>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col" id="wrap-order-list-{{ $supplier->id }}">
        @include('commons.loadablelist', [
            'identifier' => 'order-list-' . $supplier->id,
            'items' => easyFilterOrders($supplier, date('Y-m-d', strtotime('-6 months')), date('Y-m-d', strtotime('+6 months'))),
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>
