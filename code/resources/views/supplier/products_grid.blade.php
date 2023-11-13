<?php

$identifier = sprintf('products-grid-%s', $supplier->id);
$has_manual_sorting = $currentgas->manual_products_sorting;
$products = $supplier->products()->sorted()->get();

$categories = App\Category::whereNull('parent_id')->orderBy('name', 'asc')->get();
$measures = App\Measure::orderBy('name', 'asc')->get();

?>

<div class="products-grid">
    <div class="row d-none d-md-flex mb-1">
        <div class="col flowbox">
            <div class="form-group mainflow d-none d-xl-block">
                <input type="text" class="form-control table-text-filter" data-table-target="#{{ $identifier }}"  placeholder="{{ _i('Filtra') }}">
            </div>

            @include('commons.iconslegend', [
                'class' => App\Product::class,
                'target' => '#' . $identifier,
                'table_filter' => true,
                'contents' => $products
            ])
        </div>
    </div>

    <x-larastrap::form classes="inner-form" method="POST" :action="url('products/massiveupdate')">
        <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

        <div class="row">
            <div class="col">
                <table class="table sortable-table" id="{{ $identifier }}">
                    <thead>
                        <tr>
                            @if($has_manual_sorting)
                                <th width="5%"></th>
                            @endif

                            <th width="5%"><x-larastrap::check classes="triggers-all-checkbox skip-on-submit" data-target-class="product-select" squeeze switch="false" /></th>
                            <th width="40%">{{ _i('Nome') }}</th>
                            <th width="15%">{{ _i('Categoria') }}</th>
                            <th width="15%">{{ _i('Unità di Misura') }}</th>
                            <th width="10%">{{ _i('Prezzo Unitario') }}</th>
                            <th width="10%">{{ _i('Disponibile') }}</th>
                            <th width="5%">{{ _i('Ordinabile') }}</th>
                        </tr>
                    </thead>
                    <thead class="massive-actions hidden">
                        <tr>
                            @if($has_manual_sorting)
                                <th width="5%"></th>
                            @endif

                            <th width="5%"></th>
                            <th width="40%"><x-larastrap::button classes="remove_all skip-on-submit" :label="_i('Elimina')" color="danger" /></th>
                            <th width="12.5%"><x-larastrap::selectobj classes="skip-on-submit" name="category_id_all" :options="$categories" :extraitem="[0 => _i('Non Modificare')]" squeeze /></th>
                            <th width="12.5%"><x-larastrap::selectobj classes="skip-on-submit" name="measure_id_all" :options="$measures" :extraitem="[0 => _i('Non Modificare')]" squeeze /></th>
                            <th width="10%"></th>
                            <th width="10%"></th>
                            <th width="5%"><x-larastrap::check classes="skip-on-submit" name="active_all" squeeze /></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                            <x-larastrap::enclose :obj="$product">
                                <tr data-element-id="{{ $product->id }}">
                                    @if($has_manual_sorting)
                                        <td>
                                            <span class="btn btn-info sorter"><i class="bi bi-arrow-down-up"></i></span>
                                        </td>
                                    @endif

                                    <td>
                                        <x-larastrap::check name="selected[]" classes="product-select skip-on-submit" squeeze :value="$product->id" switch="false" />
                                    </td>
                                    <td>
                                        <div class="hidden">
                                            @foreach($product->icons() as $icon)
                                                <i class="bi-{{ $icon }}"></i>
                                            @endforeach
                                        </div>

                                        <x-larastrap::hidden name="id" npostfix="[]" />
                                        <x-larastrap::text name="name" :label="_i('Nome')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>
                                    <td>
                                        <x-larastrap::selectobj name="category_id" :label="_i('Categoria')" :options="$categories" squeeze :nprefix="$product->id . '-'" />
                                    </td>
                                    <td>
                                        <x-larastrap::selectobj name="measure_id" :label="_i('Unità di Misura')" :options="$measures" squeeze :nprefix="$product->id . '-'" />
                                    </td>
                                    <td>
                                        <x-larastrap::price name="price" :label="_i('Prezzo Unitario')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>
                                    <td>
                                        <x-larastrap::decimal name="max_available" :label="_i('Disponibile')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>
                                    <td>
                                        <x-larastrap::check name="active" classes="bookable" :label="_i('Ordinabile')" squeeze :nprefix="$product->id . '-'" />
                                    </td>
                                </tr>
                            </x-larastrap::enclose>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</div>
