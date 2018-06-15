<div class="row">
    <div class="col-md-6">
        <div class="form-horizontal form-filler" data-action="{{ url('orders/search') }}" data-toggle="validator" data-fill-target="#wrap-order-list-{{ $supplier->id }}">
            @include('commons.hiddenfield', ['prefix' => 'supplier_', 'name' => 'id', 'obj' => $supplier, 'extra_class' => 'enforce_filter'])
            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 months'),
                'end_date' => strtotime('+1 months'),
            ])

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-12" id="wrap-order-list-{{ $supplier->id }}">
        @include('commons.loadablelist', [
            'identifier' => 'order-list-' . $supplier->id,
            'items' => App\Aggregate::easyFilter($supplier, date('Y-m-d', strtotime('-6 months')), date('Y-m-d', strtotime('+6 months'))),
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>
