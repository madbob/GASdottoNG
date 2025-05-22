<?php

$summary = $master_summary->orders[$order->id];

$columns = $currentgas->orders_display_columns;
$table_identifier = 'summary-' . sanitizeId($order->id);
$display_columns = App\Order::displayColumns();

$pending_modifiers = $order->applyModifiers($master_summary, 'pending');
$shipped_modifiers = $order->applyModifiers($master_summary, 'shipped');

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

unset($display_columns['selection']);
unset($display_columns['notes']);

?>

<div class="order-summary-wrapper">
    <div class="row d-none d-md-flex mb-1">
        <div class="col flowbox">
            <div class="form-group mainflow d-none d-xl-block">
                <input type="text" class="form-control table-text-filter" data-table-target="#{{ $table_identifier }}" tplaceholder="generic.do_filter">
            </div>

            @include('commons.columns', [
                'columns' => $columns,
                'display_columns' => $display_columns,
                'target' => $table_identifier,
            ])
        </div>
    </div>

    <div class="table-responsive">
        <table class="table order-summary" id="{{ $table_identifier }}">
            <thead>
                <tr>
                    @foreach($display_columns as $identifier => $metadata)
                        <th scope="col" width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($order->supplier->products()->sorted()->get() as $product)
                    @if($order->hasProduct($product))
                        @php

                        if(isset($summary->products[$product->id])) {
                            $unit_price = $summary->products[$product->id]->current_price ?? $product->price;
                            $quantity_pieces = $summary->products[$product->id]->quantity_pieces ?? 0;
                            $quantity = $summary->products[$product->id]->quantity ?? 0;
                            $price = $summary->products[$product->id]->price ?? 0;
                            $delivered = $summary->products[$product->id]->delivered ?? 0;
                            $price_delivered = $summary->products[$product->id]->price_delivered ?? 0;
                            $notes = $summary->products[$product->id]->notes ?? false;
                        }
                        else {
                            $unit_price = $product->price;
                            $quantity_pieces = 0;
                            $quantity = 0;
                            $price = 0;
                            $delivered = 0;
                            $price_delivered = 0;
                            $notes = false;
                        }

                        @endphp

                        <tr data-product-id="{{ $product->id }}">
                            <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                                <input type="hidden" name="enabled[]" value="{{ $product->id }}">
                                <label class="static-label text-filterable-cell">{{ $product->printableName() }}</label>
                            </td>

                            <td class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                                {{ printablePriceCurrency($unit_price) }}
                            </td>

                            <td class="order-cell-available {{ in_array('available', $columns) ? '' : 'hidden' }}">
                                {{ printableQuantity($product->max_available, $product->measure->discrete) }}
                            </td>

                            @foreach($products_modifiers as $pmod_id => $pmod)
                                <td class="order-cell-modifier-pending-{{ $pmod_id }} {{ in_array('modifier-pending-' . $pmod_id, $columns) ? '' : 'hidden' }}">
                                    <label>{{ printablePriceCurrency($pmod->pending[$product->id] ?? 0) }}</label>
                                </td>
                                <td class="order-cell-modifier-shipped-{{ $pmod_id }} {{ in_array('modifier-shipped-' . $pmod_id, $columns) ? '' : 'hidden' }}">
                                    <label>{{ printablePriceCurrency($pmod->shipped[$product->id] ?? 0) }}</label>
                                </td>
                            @endforeach

                            <td class="order-cell-unit_measure {{ in_array('unit_measure', $columns) ? '' : 'hidden' }}">
                                {{ $product->printableMeasure(true) }}
                            </td>

                            <td class="order-cell-quantity {{ in_array('quantity', $columns) ? '' : 'hidden' }}">
                                <label>
                                    @if($product->portion_quantity != 0)
                                        {{ sprintf('%d', $quantity_pieces) }} Pezzi /
                                    @endif
                                    <span class="order-summary-product-quantity">{{ $quantity }}</span> {{ $product->measure->name }}
                                </label>
                            </td>

                            <td class="order-cell-weight {{ in_array('weight', $columns) ? '' : 'hidden' }}">
                                <label class="order-summary-product-weight">{{ $summary->products[$product->id]->weight ?? 0 }} {{ $product->measure->discrete ? __('generic.kilos') : $product->measure->name }}</label>
                            </td>

                            <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                                <label class="order-summary-product-price">{{ printablePriceCurrency($price) }}</label>
                            </td>

                            <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                                <label class="order-summary-product-delivered">{{ $delivered }} {{ $product->measure->name }}</label>
                            </td>

                            <td class="order-cell-weight_delivered {{ in_array('weight_delivered', $columns) ? '' : 'hidden' }}">
                                <label class="order-summary-product-weight_delivered">{{ $summary->products[$product->id]->weight_delivered ?? 0 }} {{ $product->measure->discrete ? __('generic.kilos') : $product->measure->name }}</label>
                            </td>

                            <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                                <label class="order-summary-product-price_delivered">{{ printablePriceCurrency($price_delivered) }}</label>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>

            <thead>
                <tr>
                    @foreach($display_columns as $identifier => $metadata)
                        <th scope="col" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                            @switch($identifier)
                                @case('total_price')
                                    <span class="order-summary-order-price">{{ printablePriceCurrency($summary->price ?? 0) }}</span>
                                    @foreach($aggregated_modifiers = App\ModifiedValue::aggregateByType($pending_modifiers) as $am)
                                        <br><small>+ {{ $am->name }}: {{ printablePrice($am->amount) }}</small>
                                    @endforeach

                                    @break

                                @case('price_delivered')
                                    <span class="order-summary-order-price_delivered">{{ printablePriceCurrency($summary->price_delivered) }}</span>
                                    @foreach(App\ModifiedValue::aggregateByType($shipped_modifiers) as $am)
                                        <br><small>+ {{ $am->name }}: {{ printablePrice($am->amount) }}</small>
                                    @endforeach

                                    @break

                                @case('weight')
                                    {{ $summary->weight ?? 0 }} {{ __('generic.kilos') }}
                                    @break

                                @case('weight_delivered')
                                    {{ $summary->weight_delivered ?? 0 }} {{ __('generic.kilos') }}
                                    @break

                            @endswitch
                        </th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
</div>
