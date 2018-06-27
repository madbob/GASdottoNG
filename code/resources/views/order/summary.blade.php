<?php $columns = $currentgas->orders_display_columns ?>

<div class="btn-group pull-right order-columns-selector">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ _i('Colonne') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach(App\Order::displayColumns() as $identifier => $metadata)
            <li>
                <a href="#">
                    <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

<table class="table order-summary">
    <thead>
        <tr>
            @foreach(App\Order::displayColumns() as $identifier => $metadata)
                @if($identifier == 'selection')
                    <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}"><button class="btn btn-default btn-xs toggle-product-abilitation" data-toggle="button" aria-pressed="false" autocomplete="off">{!! _i('Tutti') !!}</button></th>
                @else
                    <th width="{{ $metadata->width }}%" class="order-cell-{{ $identifier }} {{ in_array($identifier, $columns) ? '' : 'hidden' }}">{{ $metadata->label }}</th>
                @endif
            @endforeach
        </tr>
    </thead>

    <tbody>
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
                <tr class="product-disabled hidden-sm hidden-xs" data-product-id="{{ $product->id }}">
            @else
                <tr data-product-id="{{ $product->id }}">
            @endif

                <!-- Visualizza tutti -->
                <td class="order-cell-selection {{ in_array('selection', $columns) ? '' : 'hidden' }}">
                    <input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" {{ $enabled ? 'checked' : '' }} {{ $order->isActive() ? '' : 'disabled' }} />
                </td>

                <!-- Prodotto -->
                <td class="order-cell-name {{ in_array('name', $columns) ? '' : 'hidden' }}">
                    <input type="hidden" name="productid[]" value="{{ $product->id }}" />
                    @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])
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
                    @if(!empty($product->discount))
                        <input class="discount-toggle" type="checkbox" name="discounted[]" value="{{ $product->id }}" <?php if($enabled && $product->pivot->discount_enabled) echo 'checked' ?> />
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
                            {{ sprintf('%d', $summary->products[$product->id]['quantity_pieces']) }} Pezzi /
                        @endif
                        <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</span> {{ $product->measure->name }}
                    </label>
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
            @foreach(App\Order::displayColumns() as $identifier => $metadata)
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
            @endforeach
        </tr>
    </thead>
</table>
