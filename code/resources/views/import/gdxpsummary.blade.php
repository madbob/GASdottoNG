<x-larastrap::modal :title="_i('Importa GDXP')">
    <div class="wizard_page">
        <x-larastrap::wizardform :action="url('import/gdxp?step=run')">
            <input type="hidden" name="path" value="{{ $path }}">

            @foreach($data as $supplier)
                <?php $existing = App\Supplier::where('name', $supplier->name)->orWhere('vat', $supplier->vat)->first() ?>

                @if($supplier->orders->isEmpty() == false)
                    <x-larastrap::enclose :obj="$supplier->orders->first()">
                        <x-larastrap::datepicker name="start" :label="_i('Data Apertura')" readonly disabled />
                        <x-larastrap::datepicker name="end" :label="_i('Data Chiusura')" readonly disabled />
                    </x-larastrap::enclose>
                @endif

                <x-larastrap::field :label="_i('Fornitore')">
                    <div class="radio">
                        <label>
                            <input type="radio" name="supplier_source" value="new" {{ $existing ? '' : 'checked' }}> {{ _i('Crea nuovo') }}: {{ $supplier->name }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="supplier_source" value="update" {{ $existing ? 'checked' : '' }}> {{ _i('Aggiorna fornitore esistente') }}
                        </label>
                        <x-larastrap::selectobj name="supplier_update" squeeze :options="$currentgas->suppliers" :extraitem="_i('Seleziona un fornitore')" :value="$existing ? $existing->id : 0" />
                    </div>
                </x-larastrap::field>

                <x-larastrap::field :label="_i('Prodotti')">
                    <label class="static-label text-body-secondary">
                        {{ _i('Nel file ci sono %s prodotti.', $supplier->products->count()) }}
                    </label>
                </x-larastrap::field>
            @endforeach
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
