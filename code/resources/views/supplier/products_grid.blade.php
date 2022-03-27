<?php $identifier = sprintf('products-grid-%s', $supplier->id) ?>

<div class="row d-none d-md-flex mb-1">
    <div class="col flowbox">
        <div class="form-group mainflow d-none d-xl-block">
            <input type="text" class="form-control table-text-filter" data-table-target="#{{ $identifier }}"  placeholder="{{ _i('Filtra') }}">
        </div>

        @include('commons.iconslegend', [
            'class' => 'Product',
            'target' => '#' . $identifier,
            'table_filter' => true,
            'contents' => $supplier->products
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
                        <th width="5%"></th>
                        <th width="40%">{{ _i('Nome') }}</th>
                        <th width="20%">{{ _i('Unità di Misura') }}</th>
                        <th width="20%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="10%">{{ _i('Ordinabile') }}</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $measures = App\Measure::orderBy('name', 'asc')->get() ?>
                    @foreach($supplier->products as $product)
                        <x-larastrap::enclose :obj="$product">
                            <tr data-element-id="{{ $product->id }}">
                                <td>
                                    <x-larastrap::hidden name="id" npostfix="[]" />
                                    <span class="btn btn-info sorter"><i class="bi bi-arrow-down-up"></i></span>
                                </td>
                                <td>
                                    <x-larastrap::text name="name" :label="_i('Nome')" squeeze required :nprefix="$product->id . '-'" />
                                </td>
                                <td>
                                    <x-larastrap::selectobj name="measure_id" :label="_i('Unità di Misura')" :options="$measures" squeeze :nprefix="$product->id . '-'" />
                                </td>
                                <td>
                                    <x-larastrap::price name="price" :label="_i('Prezzo Unitario')" squeeze required :nprefix="$product->id . '-'" />
                                </td>
                                <td>
                                    <x-larastrap::check name="active" :label="_i('Ordinabile')" squeeze :nprefix="$product->id . '-'" />
                                </td>
                                <td>
                                    <p class="hidden">
                                        <span class="text-filterable-cell">{{ $product->name }}</span>
                                    </p>
                                    @foreach($product->icons() as $icon)
                                        <i class="bi-{{ $icon }}"></i>
                                    @endforeach
                                </td>
                            </tr>
                        </x-larastrap::enclose>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-larastrap::form>
