<x-larastrap::mform :obj="$gas" method="PUT" :action="route('multigas.update', $gas->id)" :nodelete="$currentuser->gas->id == $gas->id">
    <div class="row">
        <div class="col-6">
            <x-larastrap::text name="name" tlabel="generic.name" required />
        </div>
    </div>

    <div class="row multigas-editor">
        <div class="col">
            <h4>{{ __('supplier.all') }}</h4>
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
            <h4>{{ __('orders.all') }}</h4>
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
    </div>
</x-larastrap::mform>
