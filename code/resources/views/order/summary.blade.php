<table class="table order-summary">
    <thead>
        <tr>
            @if($order->isActive())
                <th class="hidden-md" width="3%"><button class="btn btn-default btn-xs toggle-product-abilitation" data-toggle="button" aria-pressed="false" autocomplete="off">{!! _i('Tutti') !!}</button></th>
                <th width="20%">{{ _i('Prodotto') }}</th>
                <th width="8%">{{ _i('Prezzo') }}</th>
                <th width="8%">{{ _i('Trasporto') }}</th>
                <th width="8%">{{ _i('Disponibile') }}</th>
                <th class="hidden-md" width="4%">{{ _i('Sconto') }}</th>
                <th width="9%">{{ _i('Unità di Misura') }}</th>
                <th width="9%">{{ _i('Quantità Ordinata') }}</th>
                <th width="5%">{{ _i('Totale Prezzo') }}</th>
                <th width="5%">{{ _i('Totale Trasporto') }}</th>
                <th width="8%">{{ _i('Quantità Consegnata') }}</th>
                <th width="8%">{{ _i('Totale Consegnato') }}</th>
                <th width="7%">{{ _i('Note') }}</th>
            @elseif($order->status != 'archived')
                <th width="25%">{{ _i('Prodotto') }}</th>
                <th width="15%">{{ _i('Unità di Misura') }}</th>
                <th width="15%">{{ _i('Quantità Ordinata') }}</th>
                <th width="15%">{{ _i('Totale Trasporto') }}</th>
                <th width="15%">{{ _i('Quantità Consegnata') }}</th>
                <th width="15%">{{ _i('Totale Consegnato') }}</th>
            @else
                <th width="25%">{{ _i('Prodotto') }}</th>
                <th width="15%">{{ _i('Unità di Misura') }}</th>
                <th width="20%">{{ _i('Quantità Ordinata') }}</th>
                <th width="20%">{{ _i('Quantità Consegnata') }}</th>
                <th width="20%">{{ _i('Totale Consegnato') }}</th>
            @endif
        </tr>
    </thead>

    <tbody>
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

                @if($order->isActive())
                    <!-- Visualizza tutti -->
                    <td class="hidden-md">
                        <input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" <?php if($enabled) echo 'checked' ?> />
                    </td>
                @endif

                <!-- Prodotto -->
                <td>
                    <input type="hidden" name="productid[]" value="{{ $product->id }}" />
                    @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])
                </td>

                @if($order->isActive())
                    <!-- Prezzo -->
                    <td>
                        @include('commons.decimalfield', [
                            'obj' => $product,
                            'label' => '',
                            'prefix' => 'product_',
                            'name' => 'price',
                            'postfix' => '[]',
                            'squeeze' => true,
                            'is_price' => true
                        ])
                    </td>

                    <!-- Trasporto -->
                    <td>
                        {{-- Nota bene: "transport" è anche un parametro dell'ordine, qui metto un prefisso per evitare la collisione --}}
                        @include('commons.decimalfield', [
                            'obj' => $product,
                            'label' => '',
                            'prefix' => 'product_',
                            'name' => 'transport',
                            'postfix' => '[]',
                            'squeeze' => true,
                            'is_price' => true
                        ])
                    </td>

                    <!-- Disponibile -->
                    <td>
                        @include('commons.decimalfield', [
                            'obj' => $product,
                            'label' => '',
                            'prefix' => 'product_',
                            'name' => 'max_available',
                            'postfix' => '[]',
                            'squeeze' => true,
                            'decimals' => 3
                        ])
                    </td>

                    <!-- Sconto Prodotto -->
                    <td class="hidden-md">
                        @if(!empty($product->discount))
                            <input class="discount-toggle" type="checkbox" name="discounted[]" value="{{ $product->id }}" <?php if($enabled && $product->pivot->discount_enabled) echo 'checked' ?> />
                        @endif
                    </td>
                @endif

                <!-- Unità di Misura -->
                <td>
                    <label>{{ $product->printableMeasure(true) }}</label>
                </td>

                <!-- Quantità Ordinata -->
                <td>
                    <label>
                        @if($product->portion_quantity != 0)
                            {{ sprintf('%d', $summary->products[$product->id]['quantity_pieces']) }} Pezzi /
                        @endif
                        <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</span> {{ $product->measure->name }}
                    </label>
                </td>

                @if($order->isActive())
                    <!-- Totale Prezzo -->
                    <td>
                        <label class="order-summary-product-price">{{ $summary->products[$product->id]['price'] }} {{ $currentgas->currency }}</label>
                    </td>
                @endif

                @if($order->status != 'archived')
                    <!-- Totale Trasporto -->
                    <td>
                        <label class="order-summary-product-transport">{{ $summary->products[$product->id]['transport'] }} {{ $currentgas->currency }}</label>
                    </td>
                @endif

                <!-- Quantità Consegnata -->
                <td>
                    <label class="order-summary-product-delivered">{{ $summary->products[$product->id]['delivered'] }} {{ $product->measure->name }}</label>
                </td>

                <!-- Totale Consegnato -->
                <td>
                    <label class="order-summary-product-price_delivered">{{ $summary->products[$product->id]['price_delivered'] }} {{ $currentgas->currency }}</label>
                </td>

                @if($order->isActive())
                    <!-- Note -->
                    <td>
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
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>

    <thead>
        <tr>
            @if($order->isActive())
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="order-summary-order-price">{{ printablePrice($summary->price) }} {{ $currentgas->currency }}</th>
                <th class="order-summary-order-transport">{{ printablePrice($summary->transport) }} {{ $currentgas->currency }}</th>
                <th></th>
                <th>
                    <span class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} {{ $currentgas->currency }}</span>
                    @if($summary->transport_delivered)
                        +<br/><span class="order-summary-order-transport_delivered">{{ printablePrice($summary->transport_delivered) }} {{ $currentgas->currency }}</span>
                    @endif
                </th>
                <th></th>
            @else
                <th></th>
                <th></th>
                <th></th>

                @if($order->status != 'archived')
                    <th class="order-summary-order-transport">{{ printablePrice($summary->transport) }} {{ $currentgas->currency }}</th>
                @endif

                <th></th>
                <th>
                    <span class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} {{ $currentgas->currency }}</span>
                    @if($summary->transport_delivered)
                        +<br/><span class="order-summary-order-transport_delivered">{{ printablePrice($summary->transport_delivered) }} {{ $currentgas->currency }}</span>
                    @endif
                </th>
            @endif
        </tr>
    </thead>
</table>
