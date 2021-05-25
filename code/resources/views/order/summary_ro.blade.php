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
                @if($identifier != 'selection' && $identifier != 'discount')
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

                    <td class="order-cell-transport {{ in_array('transport', $columns) ? '' : 'hidden' }}">
                        {{ printablePriceCurrency($product->transport) }}
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
                                {{ sprintf('%d', $summary->products[$product->id]['quantity_pieces']) }} Pezzi /
                            @endif
                            <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</span> {{ $product->measure->name }}
                        </label>
                    </td>

                    <td class="order-cell-total_price {{ in_array('total_price', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-price">{{ $summary->products[$product->id]['price'] }} {{ $currentgas->currency }}</label>
                    </td>

                    <td class="order-cell-total_transport {{ in_array('total_transport', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-transport">{{ $summary->products[$product->id]['transport'] }} {{ $currentgas->currency }}</label>
                    </td>

                    <td class="order-cell-quantity_delivered {{ in_array('quantity_delivered', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-delivered">{{ $summary->products[$product->id]['delivered'] }} {{ $product->measure->name }}</label>
                    </td>

                    <td class="order-cell-price_delivered {{ in_array('price_delivered', $columns) ? '' : 'hidden' }}">
                        <label class="order-summary-product-price_delivered">{{ $summary->products[$product->id]['price_delivered'] }} {{ $currentgas->currency }}</label>
                    </td>

                    <td class="order-cell-notes {{ in_array('notes', $columns) ? '' : 'hidden' }}">
                        @if($order->isActive())
                            @if($summary->products[$product->id]['notes'])
                                <a class="btn btn-danger" disabled>
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                </a>
                            @else
                                <a class="btn btn-info" disabled>
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
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

                        @endswitch
                    </th>
                @endif
            @endforeach
        </tr>
    </thead>
</table>
