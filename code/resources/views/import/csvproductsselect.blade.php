<?php

$original_products = $supplier->products;
$categories = App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get();
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

        <x-larastrap::form method="POST" :action="url('import/csv?type=products&step=run')" :buttons="[['color' => 'success', 'type' => 'submit', 'label' => _i('Avanti')]]">
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

            <div class="row">
                <div class="col-md-6">
                    <x-larastrap::check name="reset_list" :label="_i('Disattiva prodotti di questo fornitore non inclusi nell\'elenco')" />
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th width="3%">{{ _i('Importa') }}</th>
                        <th width="15%">{{ _i('Nome') }}</th>
                        <th width="15%">{{ _i('Descrizione') }}</th>
                        <th width="8%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="8%">{{ _i('Categoria') }}</th>
                        <th width="8%">{{ _i('Unità di Misura') }}</th>
                        <th width="8%">{{ _i('Aliquota IVA') }}</th>
                        <th width="8%">{{ _i('Codice Fornitore') }}</th>
                        <th width="6%">{{ _i('Dimensione Confezione') }}</th>
                        <th width="6%">{{ _i('Ordine Minimo') }}</th>
                        <th width="6%">{{ _i('Ordinabile per Multipli') }}</th>
                        <th width="9%">{{ _i('Aggiorna') }}</th>
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
                                        <x-larastrap::selectobj name="category_id" squeeze npostfix="[]" :options="$categories" :extraitem="['new:' . $product->temp_category_name => $product->temp_category_name]" />
                                    @else
                                        <x-larastrap::selectobj name="category_id" squeeze npostfix="[]" :options="$categories" />
                                    @endif
                                </td>
                                <td>
                                    @if(isset($product->temp_measure_name))
                                        <x-larastrap::selectobj name="measure_id" squeeze npostfix="[]" :options="$measures" :extraitem="['new:' . $product->temp_measure_name => $product->temp_measure_name]" />
                                    @else
                                        <x-larastrap::selectobj name="measure_id" squeeze npostfix="[]" :options="$measures" />
                                    @endif
                                </td>
                                <td>
                                    @if(isset($product->temp_vat_rate_name))
                                        <x-larastrap::selectobj name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" :extraitem="['new:' . $product->temp_vat_rate_name => $product->temp_vat_rate_name]" />
                                    @else
                                        <x-larastrap::selectobj name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" />
                                    @endif
                                </td>
                                <td>
                                    <x-larastrap::text name="supplier_code" squeeze npostfix="[]" />
                                </td>
                                <td>
                                    <x-larastrap::number name="package_size" squeeze npostfix="[]" classes="trim-3-ddigits" />
                                </td>
                                <td>
                                    <x-larastrap::number name="min_quantity" squeeze npostfix="[]" classes="trim-3-ddigits" />
                                </td>
                                <td>
                                    <x-larastrap::number name="multiple" squeeze npostfix="[]" classes="trim-3-ddigits" />
                                </td>

                                <td>
                                    @if($original_products->isEmpty() == false)
                                        <x-larastrap::selectobj name="want_replace" squeeze npostfix="[]" :options="$original_products" :extraitem="_i('Nessuno')" />
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
        </x-larastrap::form>
    </div>
</x-larastrap::modal>
