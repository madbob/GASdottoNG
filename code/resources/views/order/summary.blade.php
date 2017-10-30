<table class="table order-summary">
    <thead>
        <tr>
            @if($order->isActive())
                <th class="hidden-md" width="5%"><button class="btn btn-default toggle-product-abilitation" data-toggle="button" aria-pressed="false" autocomplete="off">Visualizza<br/>tutti</button></th>
                <th width="17%">Prodotto</th>
                <th width="8%">Prezzo</th>
                <th width="8%">Trasporto</th>
                <th width="8%">Disponibile</th>
                <th class="hidden-md" width="5%">Sconto Prodotto</th>
                <th width="9%">Unità di Misura</th>
                <th width="9%">Quantità Ordinata</th>
                <th width="5%">Totale Prezzo</th>
                <th width="5%">Totale Trasporto</th>
                <th width="8%">Quantità Consegnata</th>
                <th width="8%">Totale Consegnato</th>
                <th width="7%">Note</th>
            @else
                <th width="25%">Prodotto</th>
                <th width="25%">Unità di Misura</th>
                <th width="25%">Quantità Consegnata</th>
                <th width="25%">Totale Consegnato</th>
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
                    <label>{{ $product->printableName() }}</label>
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

                @if($order->isActive())
                    <!-- Quantità Ordinata -->
                    <td>
                        <label>
                            @if($product->portion_quantity != 0)
                                {{ sprintf('%d', $summary->products[$product->id]['quantity_pieces']) }} Pezzi /
                            @endif
                            <span class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</span> {{ $product->measure->name }}
                        </label>
                    </td>

                    <!-- Totale Prezzo -->
                    <td>
                        <label class="order-summary-product-price">{{ $summary->products[$product->id]['price'] }} €</label>
                    </td>

                    <!-- Totale Trasporto -->
                    <td>
                        <label class="order-summary-product-transport">{{ $summary->products[$product->id]['transport'] }} €</label>
                    </td>
                @endif

                <!-- Quantità Consegnata -->
                <td>
                    <label class="order-summary-product-delivered">{{ $summary->products[$product->id]['delivered'] }} {{ $product->measure->name }}</label>
                </td>

                <!-- Totale Consegnato -->
                <td>
                    <label class="order-summary-product-price_delivered">{{ $summary->products[$product->id]['price_delivered'] }} €</label>
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
                <th class="order-summary-order-price">{{ printablePrice($summary->price) }} €</th>
                <th class="order-summary-order-transport">{{ printablePrice($summary->transport) }} €</th>
                <th></th>
                <th class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} €</th>
                <th></th>
            @else
                <th></th>
                <th></th>
                <th></th>
                <th class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} €</th>
            @endif
        </tr>
    </thead>
</table>
