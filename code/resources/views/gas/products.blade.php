<x-larastrap::accordionitem :label="_i('Fornitori e Prodotti')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="products">

            <div class="col">
                <x-larastrap::check name="manual_products_sorting" :label="_i('Permetti di riorganizzare manualmente l\'elenco dei prodotti')" :pophelp="_i('Abilitando questa opzione, nel pannello di Modifica Rapida dei prodotti dei fornitori sarà possibile forzare un ordinamento arbitrario')" />

                <x-larastrap::field :label="_i('Colonne Modifica Rapida')" :pophelp="_i('Colonne visualizzate di default nella griglia di riassunto degli ordini. È comunque sempre possibile modificare la visualizzazione dall\'interno della griglia stessa per mezzo del selettore posto in alto a destra')">
                    <?php $columns = $currentgas->products_grid_display_columns ?>
                    @foreach(App\Product::displayColumns() as $identifier => $metadata)
                        <div class="form-check form-switch">
                            <input type="checkbox" name="products_grid_display_columns[]" class="form-check-input" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                            @if(isset($metadata->help))
                                <small> - {{ $metadata->help }}</small>
                            @endif
                        </div>
                    @endforeach
                </x-larastrap::field>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
