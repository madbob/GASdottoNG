@php

$categories = App\Category::orderBy('name', 'asc')->whereNull('parent_id')->with('children')->get();
$measures = App\Measure::orderBy('name', 'asc')->get();
$vat_rates = App\VatRate::orderBy('percentage', 'asc')->get();

@endphp

<x-larastrap::modal size="fullscreen">
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
            <input type="hidden" name="sorted_fields" value="{{ join(',', $sorted_fields) }}">

            <div class="row">
                <div class="col-md-6">
                    <x-larastrap::radios name="reset_list" tlabel="imports.existing_products_action" :options="['no' => __('texts.generic.action.ignore'), 'disable' => __('texts.generic.action.disable')]" value="no" />
                </div>
            </div>

            <hr>

            <table class="table fixed-table">
                <thead>
                    <tr>
                        <th scope="col" width="5%">{{ __('texts.imports.do') }}</th>
                        <th scope="col" width="15%">{{ __('texts.generic.name') }}</th>
                        <th scope="col" width="15%">{{ __('texts.generic.description') }}</th>
                        <th scope="col" width="10%">{{ __('texts.products.prices.unit') }}</th>
                        <th scope="col" width="10%">{{ __('texts.generic.category') }}</th>
                        <th scope="col" width="10%">{{ __('texts.generic.measure') }}</th>
                        <th scope="col" width="10%">{{ __('texts.products.vat_rate') }}</th>
                        <th scope="col" width="10%">{{ __('texts.products.code') }}</th>
                        <th scope="col" width="15%">{{ __('texts.generic.update') }}</th>
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
                                        <x-larastrap::select-model name="measure_id" squeeze npostfix="[]" :options="$measures" :extra_options="['new:' . $product->temp_measure_name => $product->temp_measure_name]" :value="sprintf('new:%s', $product->temp_measure_name)" />
                                    @else
                                        <x-larastrap::select-model name="measure_id" squeeze npostfix="[]" :options="$measures" />
                                    @endif
                                </td>
                                <td>
                                    @if(isset($product->temp_vat_rate_name))
                                        <x-larastrap::select-model name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" :extra_options="['new:' . $product->temp_vat_rate_name => $product->temp_vat_rate_name]" />
                                    @else
                                        <x-larastrap::select-model name="vat_rate_id" squeeze npostfix="[]" :options="$vat_rates" :extra_options="[0 => __('texts.generic.none')]" />
                                    @endif
                                </td>
                                <td>
                                    <x-larastrap::text name="supplier_code" squeeze npostfix="[]" />
                                </td>
                                <td width="15%">
                                    @if($supplier->products->isEmpty() == false)
                                        @php

                                        $original_products = [0 => __('texts.generic.none')];
                                        if ($product->want_replace) {
                                            $original_products[$product->want_replace->id] = $product->want_replace->printableName();
                                        }

                                        @endphp
                                        <x-larastrap::select name="want_replace" squeeze npostfix="[]" :options="$original_products" :value="$product->want_replace->id ?? 0" classes="remote-select" :data-remote-url="route('products.search', ['supplier' => $supplier->id])" />
                                    @else
                                        {{ __('texts.imports.no_products') }}
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
