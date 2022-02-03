<x-larastrap::mform :obj="$gas" method="PUT" :action="route('multigas.update', $gas->id)">
    <div class="row">
        <div class="col-6">
            <x-larastrap::text name="name" :label="_i('Nome')" required />
        </div>
    </div>

    <div class="row multigas-editor">
        <div class="col">
            <h4>{{ _i('Fornitori') }}</h4>
            <ul class="list-group">
                @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                    <li class="list-group-item">
                        {{ $supplier->printableName() }}
                        <span class="float-end">
                            <input type="checkbox" data-gas="{{ $gas->id }}" data-target-type="supplier" data-target-id="{{ $supplier->id }}" {{ $gas->suppliers()->where('suppliers.id', $supplier->id)->first() != null ? 'checked' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col">
            <h4>{{ _i('Ordini') }}</h4>
            <ul class="list-group">
                @foreach(App\Aggregate::whereHas('orders', function($query) {
                    $query->whereIn('status', ['open', 'closed', 'suspended']);
                })->orderBy('id', 'asc')->get() as $aggregate)
                    <li class="list-group-item">
                        {{ $aggregate->printableName() }}
                        <span class="float-end">
                            @include('multigas.aggregate', ['gas' => $gas, 'aggregate' => $aggregate])
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($currentgas->hasFeature('shipping_places'))
            <div class="col">
                <h4>{{ _i('Luoghi di Consegna') }}</h4>
                <ul class="list-group">
                    @foreach(App\Delivery::orderBy('name', 'asc')->get() as $delivery)
                        <li class="list-group-item">
                            {{ $delivery->printableName() }}
                            <span class="float-end">
                                <input type="checkbox" data-gas="{{ $gas->id }}" data-target-type="delivery" data-target-id="{{ $delivery->id }}" {{ $gas->deliveries()->where('deliveries.id', $delivery->id)->first() != null ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-larastrap::mform>
