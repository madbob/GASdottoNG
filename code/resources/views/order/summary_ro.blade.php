<?php $columns = $currentgas->orders_display_columns ?>

<div class="btn-group pull-right order-columns-selector">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ _i('Colonne') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach(App\Order::displayColumns() as $identifier => $metadata)
            @if($identifier != 'selection' && $identifier != 'notes' && $identifier != 'discount')
                <li>
                    <a href="#">
                        <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>

<table class="table order-summary">
    <thead>
        <tr>
            @foreach(App\Order::displayColumns() as $identifier => $metadata)
                @if($identifier != 'selection' && $identifier != 'notes' && $identifier != 'discount')
                    <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                @endif
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach($order->supplier->products as $product)
            @if($order->hasProduct($product))
                <tr data-product-id="{{ $product->id }}">
                    <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
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
                                {{ sprintf('%d', $summary->products[$product->id]->quantity_pieces ?? 0) }} Pezzi /
                            @endif
                            <span class="order-summary-product-quantity">{{ $summary->products[$product->id]->quantity ?? 0 }}</span> {{ $product->measure->name }}
                        </label>
                    </td>

                    <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-price">{{ printablePriceCurrency($summary->products[$product->id]->price) }}</label>
                    </td>

                    <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-delivered">{{ $summary->products[$product->id]->delivered ?? 0 }} {{ $product->measure->name }}</label>
                    </td>

                    <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-price_delivered">{{ printablePriceCurrency($summary->products[$product->id]->price_delivered ?? 0) }}</label>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>

    <thead>
        <tr>
            @foreach(App\Order::displayColumns() as $identifier => $metadata)
                @if($identifier != 'selection' && $identifier != 'notes' && $identifier != 'discount')
                    <th class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">
                        @switch($identifier)
                            @case('total_price')
                                <span class="order-summary-order-price">{{ printablePriceCurrency($summary->price ?? 0) }}</span>
                                <?php

                                $modifiers = $order->applyModifiers();
                                $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);

                                ?>

                                @foreach($aggregated_modifiers as $am)
                                    <p>+ {{ $am->name }}: {{ printablePrice($am->amount) }}</p>
                                @endforeach

                                @break

                            @case('price_delivered')
                                <span class="order-summary-order-price_delivered">{{ printablePriceCurrency($summary->price_delivered) }}</span>
                                @break

                        @endswitch
                    </th>
                @endif
            @endforeach
        </tr>
    </thead>
</table>
