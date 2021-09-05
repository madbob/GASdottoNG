<?php

$summary = $master_summary->orders[$order->id];
$columns = $currentgas->orders_display_columns;

?>

<div class="btn-group float-end order-columns-selector">
    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
        {{ _i('Colonne') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach(App\Order::displayColumns() as $identifier => $metadata)
            @if($identifier != 'selection' && $identifier != 'notes' && $identifier != 'discount')
                <li>
                    <div class="checkbox dropdown-item">
                        <label>
                            <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                        </label>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
</div>

<div class="table-responsive">
    <table class="table order-summary">
        <thead>
            <tr>
                @foreach(App\Order::displayColumns() as $identifier => $metadata)
                    @if($identifier != 'selection' && $identifier != 'discount')
                        <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($order->supplier->products as $product)
                @if($order->hasProduct($product))
                    @php

                    if(isset($summary->products[$product->id])) {
                        $quantity_pieces = $summary->products[$product->id]->quantity_pieces ?? 0;
                        $quantity = $summary->products[$product->id]->quantity ?? 0;
                        $price = $summary->products[$product->id]->price ?? 0;
                        $delivered = $summary->products[$product->id]->delivered ?? 0;
                        $price_delivered = $summary->products[$product->id]->price_delivered ?? 0;
                        $notes = $summary->products[$product->id]->notes ?? false;
                    }
                    else {
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
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])
                        </td>

                        <td class="order-cell-price {{ in_array('price', $columns) ? '' : 'hidden' }}">
                            {{ printablePriceCurrency($product->price) }}
                        </td>

                        <td class="order-cell-available {{ in_array('available', $columns) ? '' : 'hidden' }}">
                            {{ printableQuantity($product->max_available, $product->measure->discrete) }}
                        </td>

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

                        <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-price">{{ printablePriceCurrency($price) }}</label>
                        </td>

                        <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-delivered">{{ $delivered }} {{ $product->measure->name }}</label>
                        </td>

                        <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                            <label class="order-summary-product-price_delivered">{{ printablePriceCurrency($price_delivered) }}</label>
                        </td>

                        <td class="order-cell-notes {{ in_array('notes', $columns) ? '' : 'hidden' }}">
                            @if($order->isActive())
                                @if($notes)
                                    <a class="btn btn-danger" disabled>
                                        <i class="bi-exclamation-circle"></i>
                                    </a>
                                @else
                                    <a class="btn btn-info" disabled>
                                        <i class="bi-pencil"></i>
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>

        <thead>
            <tr>
                @foreach(App\Order::displayColumns() as $identifier => $metadata)
                    @if($identifier != 'selection' && $identifier != 'discount')
                        <th class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                            @switch($identifier)
                                @case('total_price')
                                    <span class="order-summary-order-price">{{ printablePriceCurrency($summary->price ?? 0) }}</span>
                                    <?php

                                    $modifiers = $order->applyModifiers($master_summary, 'pending');
                                    $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);

                                    ?>

                                    @foreach($aggregated_modifiers as $am)
                                        <br>+ {{ $am->name }}: {{ printablePrice($am->amount) }}
                                    @endforeach

                                    @break

                                @case('price_delivered')
                                    <span class="order-summary-order-price_delivered">{{ printablePriceCurrency($summary->price_delivered) }}</span>
                                    <?php

                                    $modifiers = $order->applyModifiers($master_summary, 'shipped');
                                    $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);

                                    ?>

                                    @foreach($aggregated_modifiers as $am)
                                        <br>+ {{ $am->name }}: {{ printablePrice($am->amount) }}
                                    @endforeach

                                    @break

                            @endswitch
                        </th>
                    @endif
                @endforeach
            </tr>
        </thead>
    </table>
</div>
