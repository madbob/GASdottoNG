<?php

$columns = $currentgas->orders_display_columns;
$table_identifier = 'summary-' . sanitizeId($order->id);
$display_columns = App\Order::displayColumns();

$categories = $order->supplier->products()->pluck('category_id')->toArray();
$categories = array_unique($categories);
$categories = App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();

?>

<div class="flowbox">
    <div class="mainflow hidden-md">
        <input type="text" class="form-control table-text-filter" data-list-target="#{{ $table_identifier }}">
    </div>

    <div class="btn-group table-sorter" data-table-target="#{{ $table_identifier }}">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {{ _i('Ordina Per') }} <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="#" data-sort-by="name">{{ _i('Nome') }}</a>
                <a href="#" data-sort-by="category_name">{{ _i('Categoria') }}</a>
            </li>
        </ul>
    </div>&nbsp;

    <div class="btn-group hidden-sm hidden-xs order-columns-selector">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span>&nbsp;{{ _i('Colonne') }} <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            @foreach($display_columns as $identifier => $metadata)
                <li>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                        </label>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>&nbsp;

    @include('commons.iconslegend', [
        'class' => 'Product',
        'target' => '#' . $table_identifier,
        'table_filter' => true,
        'limit_to' => ['th'],
        'contents' => $order->supplier->products
    ])
</div>

<hr>

<table class="table order-summary" id="{{ $table_identifier }}">
    <thead>
        <tr>
            @foreach($display_columns as $identifier => $metadata)
                @if($identifier == 'selection')
                    <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}"><button class="btn btn-default btn-xs toggle-product-abilitation" data-toggle="button" aria-pressed="false" autocomplete="off">{!! _i('Tutti') !!}</button></th>
                @else
                    <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                @endif
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach($categories as $cat)
            <tr class="table-sorting-header hidden" data-sorting-category_name="{{ $cat }}">
                <td colspan="{{ count($display_columns) }}">
                    {{ $cat }}
                </td>
            </tr>
        @endforeach

        <!--
            Warning: l'ordine delle colonne qui deve riflettere l'ordine degli
            elementi restituiti da Order::displayColumns() (peraltro usata per
            generare il menu delle colonne sopra)
        -->

        @foreach($order->supplier->products as $product)
            <?php

                $enabled = $order->hasProduct($product);
                if ($order->isActive() == false & $enabled == false)
                    continue;

            ?>

            @if($enabled == false)
                <tr class="product-disabled do-not-filter hidden-sm hidden-xs" data-product-id="{{ $product->id }}" data-sorting-name="{{ $product->name }}" data-sorting-category_name="{{ $product->category_name }}">
            @else
                <tr data-product-id="{{ $product->id }}" data-sorting-name="{{ $product->name }}" data-sorting-category_name="{{ $product->category_name }}">
            @endif

                <!-- Visualizza tutti -->
                <td class="order-cell-selection {{ in_array('selection', $columns) ? '' : 'hidden' }}">
                    <input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" {{ $enabled ? 'checked' : '' }} {{ $order->isActive() ? '' : 'disabled' }} />

                    <div class="hidden">
                        @foreach($product->icons() as $icon)
                            <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>
                        @endforeach
                    </div>
                </td>

                <!-- Prodotto -->
                <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                    <input type="hidden" name="productid[]" value="{{ $product->id }}" />
                    @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product, 'extra_class' => 'text-filterable-cell'])
                </td>

                <!-- Prezzo -->
                <td class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                    @include('commons.decimalfield', [
                        'obj' => $product,
                        'label' => '',
                        'prefix' => 'product_',
                        'name' => 'price',
                        'postfix' => '[]',
                        'squeeze' => true,
                        'is_price' => true,
                        'disabled' => $order->isActive() == false
                    ])
                </td>

                <!-- Trasporto -->
                <td class="order-cell-transport {{ in_array('transport', $columns) ? '' : 'hidden' }}">
                    {{-- Nota bene: "transport" è anche un parametro dell'ordine, qui metto un prefisso per evitare la collisione --}}
                    @include('commons.decimalfield', [
                        'obj' => $product,
                        'label' => '',
                        'prefix' => 'product_',
                        'name' => 'transport',
                        'postfix' => '[]',
                        'squeeze' => true,
                        'is_price' => true,
                        'disabled' => $order->isActive() == false
                    ])
                </td>

                <!-- Disponibile -->
                <td class="order-cell-available {{ in_array('available', $columns) ? '' : 'hidden' }}">
                    @include('commons.decimalfield', [
                        'obj' => $product,
                        'label' => '',
                        'prefix' => 'product_',
                        'name' => 'max_available',
                        'postfix' => '[]',
                        'squeeze' => true,
                        'decimals' => 3,
                        'disabled' => $order->isActive() == false
                    ])
                </td>

                <!-- Sconto Prodotto -->
                <td class="order-cell-discount {{ in_array('discount', $columns) ? '' : 'hidden' }}">
                    @if(!empty($product->discount) && $product->discount != 0)
                        {{ printablePercentage($product->discount) }}
                    @endif
                </td>

                <!-- Unità di Misura -->
                <td class="order-cell-unit_measure {{ in_array('unit_measure', $columns) ? '' : 'hidden' }}">
                    <label>{{ $product->printableMeasure(true) }}</label>
                </td>

                <!-- Quantità Ordinata -->
                <td class="order-cell-quantity {{ in_array('quantity', $columns) ? '' : 'hidden' }}">
                    <label>
                        @if($product->portion_quantity != 0)
                            <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity_pieces'] }}</span> Pezzi
                        @else
                            <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</span> {{ $product->measure->name }}
                        @endif
                    </label>
                </td>

                <!-- Peso Ordinato -->
                <td class="order-cell-weight {{ in_array('weight', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-weight">{{ $summary->products[$product->id]['weight'] }} {{ $product->measure->discrete ? _i('Chili') : $product->measure->name }}</label>
                </td>

                <!-- Totale Prezzo -->
                <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-price">{{ $summary->products[$product->id]['price'] }} {{ $currentgas->currency }}</label>
                </td>

                <!-- Totale Trasporto -->
                <td class="order-cell-total_transport {{ in_array('total_transport', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-transport">{{ $summary->products[$product->id]['transport'] }} {{ $currentgas->currency }}</label>
                </td>

                <!-- Quantità Consegnata -->
                <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-delivered">{{ $summary->products[$product->id]['delivered'] }} {{ $product->measure->name }}</label>
                </td>

                <!-- Peso Consegnato -->
                <td class="order-cell-weight_delivered {{ in_array('weight_delivered', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-weight_delivered">{{ $summary->products[$product->id]['weight_delivered'] }} {{ $product->measure->discrete ? _i('Chili') : $product->measure->name }}</label>
                </td>

                <!-- Totale Consegnato -->
                <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                    <label class="order-summary-product-price_delivered">{{ $summary->products[$product->id]['price_delivered'] }} {{ $currentgas->currency }}</label>
                </td>

                <!-- Note -->
                <td class="order-cell-notes {{ in_array('notes', $columns) ? '' : 'hidden' }}">
                    @if($order->isActive())
                        <?php $random_identifier = rand(); ?>

                        @if($summary->products[$product->id]['notes'])
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#fix-{{ $random_identifier }}">
                                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            </button>
                        @else
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#fix-{{ $random_identifier }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </button>
                        @endif

                        @push('postponed')
                            <div class="modal fade dynamic-contents" id="fix-{{ $random_identifier }}" tabindex="-1" role="dialog" data-contents-url="{{ url('orders/fixes/' . $order->id . '/' . $product->id) }}">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    </div>
                                </div>
                            </div>
                        @endpush
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>

    <thead>
        <tr>
            @foreach($display_columns as $identifier => $metadata)
                <th class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                    @switch($identifier)
                        @case('total_price')
                            <span class="order-summary-order-price">{{ printablePriceCurrency($summary->price) }}</span>
                            @if($order->discount != 0)
                                <button type="button" class="btn btn-default btn-xs" data-toggle="popover" data-content="{{ printablePriceCurrency($summary->undiscounted_price) }} - Sconto {{ printablePercentage($order->discount) }}">
                                    <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
                                </button>
                            @endif
                            @break

                        @case('total_transport')
                            {{ printablePriceCurrency($summary->transport) }}
                            @break

                        @case('price_delivered')
                            <span class="order-summary-order-price_delivered">{{ printablePriceCurrency($summary->price_delivered) }}</span>
                            @if($summary->transport_delivered)
                                + <span class="order-summary-order-transport_delivered">{{ printablePriceCurrency($summary->transport_delivered) }}</span>
                            @endif
                            @if($order->discount != 0)
                                <button type="button" class="btn btn-default btn-xs" data-toggle="popover" data-content="{{ printablePriceCurrency($summary->undiscounted_price_delivered) }} - Sconto {{ printablePercentage($order->discount) }}">
                                    <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
                                </button>
                            @endif
                            @break

                        @case('weight')
                            {{ $summary->weight }} {{ _i('Chili') }}
                            @break

                        @case('weight_delivered')
                            {{ $summary->weight_delivered }} {{ _i('Chili') }}
                            @break

                    @endswitch
                </th>
            @endforeach
        </tr>
    </thead>
</table>
