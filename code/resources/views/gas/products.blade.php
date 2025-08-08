<x-larastrap::accordionitem :label_html="formatAccordionLabel('gas.suppliers_and_products', 'tags')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="products">

            <div class="col">
                <x-larastrap::check name="manual_products_sorting" tlabel="gas.manual_products_sorting" tpophelp="gas.help.manual_products_sorting" />

                <x-larastrap::field tlabel="gas.fast_product_change_columns" tpophelp="gas.help.fast_product_change_columns">
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
