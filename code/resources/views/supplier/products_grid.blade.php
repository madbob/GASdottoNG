<?php $identifier = sprintf('products-grid-%s', $supplier->id) ?>

<div class="row">
    <div class="col-md-12 flowbox">
        <div class="form-group mainflow hidden-md">
            <input type="text" class="form-control table-text-filter" data-list-target="#{{ $identifier }}">
        </div>
        <div>
            @if(!empty($filters))
                <div class="btn-group hidden-xs hidden-sm list-filters" role="group" aria-label="Filtri" data-list-target="#{{ $identifier }}">
                    @foreach($filters as $attribute => $info)
                        <button type="button" class="btn btn-default" data-filter-attribute="{{ $attribute }}">
                            <span class="glyphicon glyphicon-{{ $info->icon }}" aria-hidden="true"></span>&nbsp;{{ $info->label }}
                        </button>
                    @endforeach
                </div>
            @endif

            @include('commons.iconslegend', [
                'class' => 'Product',
                'target' => '#' . $identifier,
                'table_filter' => true,
                'contents' => $supplier->products
            ])
        </div>
    </div>
</div>

<form class="inner-form" method="POST" action="{{ url('products/massiveupdate') }}">
    <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

    <div class="row">
        <div class="col-md-12">
            <table class="table" id="{{ $identifier }}">
                <thead>
                    <tr>
                        <th width="45%">{{ _i('Nome') }}</th>
                        <th width="15%">{{ _i('Unità di Misura') }}</th>
                        <th width="15%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="15%">{{ _i('Prezzo Trasporto') }}</th>
                        <th width="5%">{{ _i('Ordinabile') }}</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $measures = App\Measure::orderBy('name', 'asc')->get() ?>
                    @foreach($supplier->products as $product)
                        <tr data-element-id="{{ $product->id }}">
                            <td>
                                @include('commons.hiddenfield', [
                                    'obj' => $product,
                                    'name' => 'id',
                                    'postfix' => '[]'
                                ])

                                @include('commons.textfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'name',
                                    'label' => _i('Nome'),
                                    'squeeze' => true,
                                    'mandatory' => true
                                ])
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'measure_id',
                                    'objects' => $measures,
                                    'label' => _i('Unità di Misura'),
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'price',
                                    'label' => _i('Prezzo Unitario'),
                                    'squeeze' => true,
                                    'is_price' => true,
                                    'mandatory' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'transport',
                                    'label' => _i('Prezzo Trasporto'),
                                    'squeeze' => true,
                                    'is_price' => true
                                ])
                            </td>
                            <td>
                                @include('commons.boolfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'active',
                                    'label' => _i('Ordinabile'),
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                <p class="hidden">
                                    <span class="text-filterable-cell">{{ $product->name }}</span>
                                </p>
                                @foreach($product->icons() as $icon)
                                    <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right" role="group">
                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>
