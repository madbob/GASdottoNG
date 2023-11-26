<?php

$columns = $currentgas->products_grid_display_columns;

if ($currentgas->manual_products_sorting == false) {
    $columns = array_filter($columns, fn($c) => $c != 'sorting');
}

$display_columns = App\Product::displayColumns();

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

            @include('commons.columns', [
                'columns' => $columns,
                'display_columns' => $display_columns,
                'target' => $identifier,
            ])

            &nbsp;

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
                            @foreach($display_columns as $identifier => $metadata)
                                <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                                    @if($identifier == 'selection')
                                        <x-larastrap::check classes="triggers-all-checkbox skip-on-submit" data-target-class="product-select" squeeze switch="false" />
                                    @else
                                        {{ $metadata->label }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <thead class="massive-actions hidden">
                        <tr>
                            <th class="order-cell-sorting {{ in_array('sorting', $columns) ? '' : 'hidden' }}">
                                &nbsp;
                            </th>

                            <th class="order-cell-selection {{ in_array('selection', $columns) ? '' : 'hidden' }}">
                                &nbsp;
                            </th>

                            <th class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                                <x-larastrap::button classes="remove_all skip-on-submit" :label="_i('Elimina')" color="danger" />
                            </th>

                            <th class="order-cell-category {{ in_array('category', $columns) ? '' : 'hidden' }}">
                                <x-larastrap::selectobj classes="skip-on-submit" name="category_id_all" :options="$categories" :extraitem="[0 => _i('Non Modificare')]" squeeze />
                            </th>

                            <th class="order-cell-measure {{ in_array('measure', $columns) ? '' : 'hidden' }}">
                                <x-larastrap::selectobj classes="skip-on-submit" name="measure_id_all" :options="$measures" :extraitem="[0 => _i('Non Modificare')]" squeeze />
                            </th>

                            <th class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                                &nbsp;
                            </th>

                            <th class="order-cell-max_available {{ in_array('max_available', $columns) ? '' : 'hidden' }}">
                                &nbsp;
                            </th>

                            <th class="order-cell-active {{ in_array('active', $columns) ? '' : 'hidden' }}">
                                <x-larastrap::check classes="skip-on-submit" name="active_all" squeeze />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                            <x-larastrap::enclose :obj="$product">
                                <tr data-element-id="{{ $product->id }}">
                                    <td class="order-cell-sorting {{ in_array('sorting', $columns) ? '' : 'hidden' }}">
                                        <span class="btn btn-info sorter"><i class="bi bi-arrow-down-up"></i></span>
                                    </td>

                                    <td class="order-cell-selection {{ in_array('selection', $columns) ? '' : 'hidden' }}">
                                        <x-larastrap::check name="selected[]" classes="product-select skip-on-submit" squeeze :value="$product->id" switch="false" />
                                    </td>

                                    <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                                        <div class="hidden">
                                            <span class="text-filterable-cell">{{ $product->name }}</span>
                                            @foreach($product->icons() as $icon)
                                                <i class="bi-{{ $icon }}"></i>
                                            @endforeach
                                        </div>

                                        <x-larastrap::hidden name="id" npostfix="[]" />
                                        <x-larastrap::text name="name" :label="_i('Nome')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>

                                    <td class="order-cell-category {{ in_array('category', $columns) ? '' : 'hidden' }}">
                                        <x-larastrap::selectobj name="category_id" :label="_i('Categoria')" :options="$categories" squeeze :nprefix="$product->id . '-'" />
                                    </td>

                                    <td class="order-cell-measure {{ in_array('measure', $columns) ? '' : 'hidden' }}">
                                        <x-larastrap::selectobj name="measure_id" :label="_i('Unità di Misura')" :options="$measures" squeeze :nprefix="$product->id . '-'" />
                                    </td>

                                    <td class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                                        <x-larastrap::price name="price" :label="_i('Prezzo Unitario')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>

                                    <td class="order-cell-max_available {{ in_array('max_available', $columns) ? '' : 'hidden' }}">
                                        <x-larastrap::decimal name="max_available" :label="_i('Disponibile')" squeeze required :nprefix="$product->id . '-'" />
                                    </td>

                                    <td class="order-cell-active {{ in_array('active', $columns) ? '' : 'hidden' }}">
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
