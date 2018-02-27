<form class="form-horizontal main-form multigas-editor" method="PUT" action="{{ route('multigas.update', $gas->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
        </div>
        <div class="col-md-6">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <ul class="list-group">
                @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                    <li class="list-group-item">
                        {{ $supplier->printableName() }}
                        <span class="pull-right">
                            <input type="checkbox" data-toggle="toggle" data-size="mini" data-gas="{{ $gas->id }}" data-target-type="supplier" data-target-id="{{ $supplier->id }}" {{ $gas->suppliers()->where('suppliers.id', $supplier->id)->first() != null ? 'checked' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="list-group">
                @foreach(App\Aggregate::whereHas('orders', function($query) {
                    $query->whereIn('status', ['open', 'closed', 'suspended']);
                })->orderBy('id', 'asc')->get() as $aggregate)
                    <li class="list-group-item">
                        {{ $aggregate->printableName() }}
                        <span class="pull-right">
                            <input type="checkbox" data-toggle="toggle" data-size="mini" data-gas="{{ $gas->id }}" data-target-type="aggregate" data-target-id="{{ $aggregate->id }}" {{ $gas->aggregates()->where('aggregates.id', $aggregate->id)->first() != null ? 'checked' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="list-group">
                @foreach(App\Delivery::orderBy('name', 'asc')->get() as $delivery)
                    <li class="list-group-item">
                        {{ $delivery->printableName() }}
                        <span class="pull-right">
                            <input type="checkbox" data-toggle="toggle" data-size="mini" data-gas="{{ $gas->id }}" data-target-type="delivery" data-target-id="{{ $delivery->id }}" {{ $gas->deliveries()->where('deliveries.id', $delivery->id)->first() != null ? 'checked' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @include('commons.formbuttons', ['obj' => $gas, 'no_save' => true])
</form>
