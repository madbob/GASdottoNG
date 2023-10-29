<?php

$categories = App\Category::orderBy('name', 'asc')->whereNull('parent_id')->with('children')->get();
$measures = App\Measure::orderBy('name', 'asc')->get();
$vat_rates = App\VatRate::orderBy('percentage', 'asc')->get();

?>

<x-larastrap::modal :title="_i('Importa CSV')" size="fullscreen">
    <div class="wizard_page">
        @if(!empty($errors))
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-larastrap::wizardform :action="url('import/csv?type=products&step=run')">
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

            <div class="row">
                <div class="col-md-6">
                    <x-larastrap::radios name="reset_list" :label="_i('Prodotti Esistenti')" :options="['no' => _i('Ignora'), 'disable' => _i('Disabilita'), 'remove' => _i('Elimina')]" value="no" />
                </div>
            </div>

            <hr>

            <table class="table fixed-table">
                <thead>
                    <tr>
                        <th width="5%">{{ _i('Importa') }}</th>
                        <th width="15%">{{ _i('Nome') }}</th>
                        <th width="15%">{{ _i('Descrizione') }}</th>
                        <th width="10%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="10%">{{ _i('Categoria') }}</th>
                        <th width="10%">{{ _i('Unità di Misura') }}</th>
                        <th width="10%">{{ _i('Aliquota IVA') }}</th>
                        <th width="10%">{{ _i('Codice Fornitore') }}</th>
                        <th width="15%">{{ _i('Aggiorna') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                        <x-larastrap::enclose :obj="$product">
                            <tr>
                                <td>
                                    <input type="checkbox" name="import[]" value="{{ $index }}" checked>
                                </td>
                                <td>
                                    <x-larastrap::hidden name="weight" squeeze npostfix="[]" />
                                    <x-larastrap::hidden name="package_size" squeeze npostfix="[]" />
                                    <x-larastrap::hidden name="min_quantity" squeeze npostfix="[]" />
                                    <x-larastrap::hidden name="multiple" squeeze npostfix="[]" />
                                    <x-larastrap::hidden name="portion_quantity" squeeze npostfix="[]" />

                                    <x-larastrap::text name="name" squeeze npostfix="[]" />
                                </td>
                                <td>
                                    <x-larastrap::text name="description" squeeze npostfix="[]" />
                                </td>
                                <td>
                                    <x-larastrap::price name="price" squeeze npostfix="[]" />
                                </td>
                                <td>
                                    @if(isset($product->temp_category_name))
                                        <x-larastrap::selectobj name="category_id" squeeze npostfix="[]" :options="$categories" :extraitem="['new:' . $product->temp_category_name => $product->temp_category_name]" :value="sprintf('new:%s', $product->temp_category_name)" />
                                    @else
                                        <x-larastrap::selectobj name="category_id" squeeze npostfix="[]" :options="$categories" />
                                    @endif
                                </td>
                                <td>
                                    @if(isset($product->temp_measure_name))
                                        <x-larastrap::selectobj name="measure_id" squeeze npostfix="[]" :options="$measures" :extraitem="['new:' . $product->temp_measure_name => $product->temp_measure_name]" :value="sprintf('new:%s', $product->temp_measure_name)" />
                                    @else
                                        <x-larastrap::selectobj name="measure_id" squeeze npostfix="[]" :options="$measures" />
                                    @endif
                                </td>
                                <td>
                                    @if(isset($product->temp_vat_rate_name))
                                        <x-larastrap::selectobj name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" :extraitem="['new:' . $product->temp_vat_rate_name => $product->temp_vat_rate_name]" />
                                    @else
                                        <x-larastrap::selectobj name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" :extraitem="['0' => _i('Nessuna')]" />
                                    @endif
                                </td>
                                <td>
                                    <x-larastrap::text name="supplier_code" squeeze npostfix="[]" />
                                </td>
                                <td width="15%">
                                    @if($supplier->products->isEmpty() == false)
                                        @php

                                        $original_products = [0 => _i('Nessuno')];
                                        if ($product->want_replace) {
                                            $original_products[$product->want_replace->id] = $product->want_replace->printableName();
                                        }

                                        @endphp
                                        <x-larastrap::select name="want_replace" squeeze npostfix="[]" :options="$original_products" :value="$product->want_replace->id ?? 0" classes="remote-select" :data-remote-url="route('products.search', ['supplier' => $supplier->id])" />
                                    @else
                                        {{ _i('Nessun Prodotto Aggiornabile') }}
                                        <input type="hidden" name="want_replace[]" value="0">
                                    @endif
                                </td>
                            </tr>
                        </x-larastrap::enclose>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
