<x-larastrap::modal>
    <div class="wizard_page">
        <x-larastrap::wizardform :action="url('import/gdxp?step=run')">
            <input type="hidden" name="path" value="{{ $path }}">

            @foreach($data as $supplier)
                <?php $existing = App\Supplier::where('name', $supplier->name)->orWhere('vat', $supplier->vat)->first() ?>

                @if($supplier->orders->isEmpty() == false)
                    <x-larastrap::enclose :obj="$supplier->orders->first()">
                        <x-larastrap::datepicker name="start" tlabel="orders.dates.start" readonly disabled />
                        <x-larastrap::datepicker name="end" tlabel="orders.dates.end" readonly disabled />
                    </x-larastrap::enclose>
                @endif

                <x-larastrap::field tlabel="orders.supplier">
                    <div class="radio">
                        <label>
                            <input type="radio" name="supplier_source" value="new" {{ $existing ? '' : 'checked' }}> {{ __('generic.create') }}: {{ $supplier->name }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="supplier_source" value="update" {{ $existing ? 'checked' : '' }}> {{ __('imports.update_supplier') }}
                        </label>
                        <x-larastrap::select-model name="supplier_update" squeeze :options="$currentgas->suppliers" :extra_options="[0 => __('imports.select_supplier')]" :value="$existing ? $existing->id : 0" />
                    </div>
                </x-larastrap::field>

                <x-larastrap::field tlabel="products.list">
                    <label class="static-label text-body-secondary">
                        {{ __('imports.products_count', ['count' => $supplier->products->count()]) }}
                    </label>
                </x-larastrap::field>
            @endforeach
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
