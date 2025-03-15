<?php

$summary = $master_summary->orders[$order->id];

$columns = $currentgas->orders_display_columns;
$table_identifier = 'summary-' . sanitizeId($order->id);
$display_columns = App\Order::displayColumns();

$products = $order->supplier->products()->with(['category'])->withTrashed()->sorted()->get();
$order_products = $order->products()->with(['category'])->sorted()->get();
$categories = $products->pluck('category_id')->toArray();
$categories = array_unique($categories);
$categories = App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();

$pending_modifiers = $order->applyModifiers($master_summary, 'pending');

if (isset($shipped_modifiers) == false) {
    $shipped_modifiers = $order->applyModifiers($master_summary, 'shipped');
}

$products_modifiers = [];
App\ModifiedValue::organizeForProducts($products_modifiers, $pending_modifiers, 'pending');
App\ModifiedValue::organizeForProducts($products_modifiers, $shipped_modifiers, 'shipped');

foreach($display_columns as $identifier => $metadata) {
    if (Illuminate\Support\Str::startsWith($identifier, 'modifier-')) {
        $mod_id = preg_replace('/modifier-[a-z]*-(.*)/', '\1', $identifier);
        if (!isset($products_modifiers[$mod_id])) {
            unset($display_columns[$identifier]);
        }
    }
}

?>

<div class="order-summary-wrapper" data-reload-url="{{ route('orders.show', ['order' => $order->id, 'format' => 'summary']) }}">
    <div class="row d-none d-md-flex mb-1">
        <div class="col flowbox">
            <div class="form-group mainflow d-none d-xl-block">
                <input type="text" class="form-control table-text-filter" data-table-target="#{{ $table_identifier }}" placeholder="{{ _i('Filtra') }}">
            </div>

            <div class="btn-group table-sorter" data-table-target="#{{ $table_identifier }}">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    {{ _i('Ordina Per') }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#" class="dropdown-item" data-sort-by="sorting" data-numeric-sorting="true">{{ _i('Ordinamento Manuale') }}</a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item" data-sort-by="name">{{ _i('Nome') }}</a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item" data-sort-by="category_name">{{ _i('Categoria') }}</a>
                    </li>
                </ul>
            </div>

            @include('commons.columns', [
                'columns' => $columns,
                'display_columns' => $display_columns,
                'target' => $table_identifier,
            ])

            &nbsp;

            @include('commons.iconslegend', [
                'class' => App\Product::class,
                'target' => '#' . $table_identifier,
                'table_filter' => true,
                'limit_to' => ['th'],
                'contents' => $products
            ])
        </div>
    </div>

    <div class="table-responsive">
        <table class="table order-summary" id="{{ $table_identifier }}">
            <thead>
                <tr>
                    @foreach($display_columns as $identifier => $metadata)
                        @if($identifier == 'selection')
                            <th scope="col" width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                                @if($products->count() != $order_products->count())
                                    <button class="btn btn-light btn-sm toggle-product-abilitation" data-bs-toggle="button">{!! _i('Vedi Tutti') !!}</button>
                                @endif
                            </th>
                        @else
                            <th scope="col" width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($categories as $cat)
                    <tr class="table-sorting-header d-none" data-sorting-category_name="{{ $cat }}">
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

                @foreach($products as $product)
                    <?php

                    $enabled = $order->hasProduct($product);
                    if ($enabled == false) {
                        if ($product->deleted_at) {
                            continue;
                        }

                        if ($order->isActive() == false) {
                            continue;
                        }
                    }
                    else {
                        $product = $order_products->firstWhere('id', $product->id);
                    }

                    ?>

                    @if($enabled == false)
                        <tr class="product-disabled do-not-filter" data-product-id="{{ $product->id }}" data-sorting-name="{{ $product->name }}" data-sorting-sorting="{{ $product->sorting }}" data-sorting-category_name="{{ $product->category_name }}">
                    @else
                        <tr data-product-id="{{ $product->id }}" data-sorting-name="{{ $product->name }}" data-sorting-sorting="{{ $product->sorting }}" data-sorting-category_name="{{ $product->category_name }}">
                    @endif

                        <!-- Visualizza tutti -->
                        <td class="order-cell-selection {{ in_array('selection', $columns) ? '' : 'hidden' }}">
                            <input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" {{ $enabled ? 'checked' : '' }} {{ $order->isActive() ? '' : 'disabled' }} />
                            @if($order->isActive() == false)
                                <input type="hidden" name="enabled[]" value="{{ $product->id }}">
                            @endif

                            <div class="visually-hidden">
                                @foreach($product->icons() as $icon)
                                    <i class="bi-{{ $icon }}"></i>
                                @endforeach
                            </div>
                        </td>

                        <!-- Prodotto -->
                        <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                            <input type="hidden" name="productid[]" value="{{ $product->id }}" />

							@can('supplier.modify', $order->supplier)
                                <label class="static-label text-filterable-cell d-none d-xl-inline-block">{{ $product->printableName() }}</label>

                                @php
                                $edit_url = route('products.show', ['product' => $product->id, 'format' => 'modal']);
                                @endphp

                                <label class="static-label d-inline-block d-xl-none text-primary async-modal" data-modal-url="{{ $edit_url }}">{{ $product->printableName() }}</label>

	                            <div class="float-end">
	                                <a href="{{ $edit_url }}" class="btn btn-xs btn-info async-modal d-none d-md-inline-block">
	                                    <i class="bi-pencil"></i>
	                                </a>
	                            </div>
                            @else
                                <label class="static-label text-filterable-cell">{{ $product->printableName() }}</label>
							@endcan
                        </td>

                        <!-- Prezzo -->
                        <td class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                            <label>{{ printablePriceCurrency($product->getPrice(false)) }}</label>
                        </td>

                        <!-- Disponibile -->
                        <td class="order-cell-available {{ in_array('available', $columns) ? '' : 'hidden' }}">
                            <label>
                                @if($product->max_available != 0)
                                    @if($product->portion_quantity != 0)
                                        {{ round($product->max_available / $product->portion_quantity, 3) }} Pezzi ({{ $product->max_available }} {{ $product->measure->name }})
                                    @else
                                        {{ $product->max_available }} {{ $product->measure->name }}
                                    @endif
                                @else
                                    -
                                @endif
                            </label>
                        </td>

                        <!-- Unità di Misura -->
                        <td class="order-cell-unit_measure {{ in_array('unit_measure', $columns) ? '' : 'hidden' }}">
                            <label>{{ $product->printableMeasure(true) }}</label>
                        </td>

                        <!-- Modificatori sui Prodotti -->
                        @foreach($products_modifiers as $pmod_id => $pmod)
                            <td class="order-cell-modifier-pending-{{ $pmod_id }} {{ in_array('modifier-pending-' . $pmod_id, $columns) ? '' : 'hidden' }}">
                                <label>{{ printablePriceCurrency($pmod->pending[$product->id] ?? 0) }}</label>
                            </td>
                            <td class="order-cell-modifier-shipped-{{ $pmod_id }} {{ in_array('modifier-shipped-' . $pmod_id, $columns) ? '' : 'hidden' }}">
                                <label>{{ printablePriceCurrency($pmod->shipped[$product->id] ?? 0) }}</label>
                            </td>
                        @endforeach

                        <!-- Quantità Ordinata -->
                        <td class="order-cell-quantity {{ in_array('quantity', $columns) ? '' : 'hidden' }}">
                            <label>
                                @if($product->portion_quantity != 0)
                                    <span class="order-summary-product-quantity">{{ $summary->products[$product->id]->quantity_pieces ?? 0 }}</span> Pezzi
                                @else
                                    <span class="order-summary-product-quantity">{{ $summary->products[$product->id]->quantity ?? 0 }}</span> {{ $product->measure->name }}
                                @endif
                            </label>
                        </td>

                        <!-- Peso Ordinato -->
                        <td class="order-cell-weight {{ in_array('weight', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-weight">{{ $summary->products[$product->id]->weight ?? 0 }} {{ $product->measure->discrete ? _i('Chili') : $product->measure->name }}</label>
                        </td>

                        <!-- Totale Prezzo -->
                        <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-price">{{ printablePriceCurrency($summary->products[$product->id]->price ?? 0) }}</label>
                        </td>

                        <!-- Quantità Consegnata -->
                        <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-delivered">{{ $summary->products[$product->id]->delivered ?? 0 }} {{ $product->measure->name }}</label>
                        </td>

                        <!-- Peso Consegnato -->
                        <td class="order-cell-weight_delivered {{ in_array('weight_delivered', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-weight_delivered">{{ $summary->products[$product->id]->weight_delivered ?? 0 }} {{ $product->measure->discrete ? _i('Chili') : $product->measure->name }}</label>
                        </td>

                        <!-- Totale Consegnato -->
                        <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-price_delivered">{{ printablePriceCurrency($summary->products[$product->id]->price_delivered ?? 0) }}</label>
                        </td>

                        <!-- Note -->
                        <td class="order-cell-notes {{ in_array('notes', $columns) ? '' : 'hidden' }}">
                            @if($order->isActive())
                                @if($product->hasWarningWithinOrder($summary))
                                    <a href="{{ url('orders/fixes/' . $order->id . '/' . $product->id) }}" class="btn btn-danger async-modal">
                                        <i class="bi-exclamation-circle"></i>
                                    </a>
                                @else
                                    <a href="{{ url('orders/fixes/' . $order->id . '/' . $product->id) }}" class="btn btn-info async-modal">
                                        <i class="bi-pencil"></i>
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <thead>
                <tr>
                    @foreach($display_columns as $identifier => $metadata)
                        <th scope="col" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                            @switch($identifier)
                                @case('total_price')
                                    <span class="order-summary-order-price">{{ printablePriceCurrency($summary->price ?? 0) }}</span>
                                    @foreach(App\ModifiedValue::aggregateByType($pending_modifiers) as $am)
                                        <br><small>+ {{ $am->name }}: {{ printablePrice($am->amount) }}</small>
                                    @endforeach

                                    @break

                                @case('price_delivered')
                                    <span class="order-summary-order-price_delivered">
                                        {{ printablePriceCurrency($summary->price_delivered ?? 0) }}
                                        @if($order->bookings()->where('status', 'saved')->count() != 0)
                                            <span class="ms-2 text-black" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{{ _i('Le quantità di alcune prenotazioni in questo ordine sono salvate ma non risultano ancora effettivamente consegnate né pagate.') }}">
                                                <i class="bi-exclamation-circle"></i>
                                            </span>
                                        @endif
                                    </span>
                                    @foreach(App\ModifiedValue::aggregateByType($shipped_modifiers) as $am)
                                        <br><small>+ {{ $am->name }}: {{ printablePrice($am->amount) }}</small>
                                    @endforeach

                                    @break

                                @case('weight')
                                    {{ $summary->weight ?? 0 }} {{ _i('Chili') }}
                                    @break

                                @case('weight_delivered')
                                    {{ $summary->weight_delivered ?? 0 }} {{ _i('Chili') }}
                                    @break

                            @endswitch
                        </th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
</div>
