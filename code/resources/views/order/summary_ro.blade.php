<table class="table order-summary">
    <thead>
        <tr>
            @if($order->isActive())
                <th width="19%">Prodotto</th>
                <th width="9%">Prezzo</th>
                <th width="9%">Trasporto</th>
                <th width="9%">Disponibile</th>
                <th width="9%">Unità di Misura</th>
                <th width="9%">Quantità Ordinata</th>
                <th width="9%">Totale Prezzo</th>
                <th width="9%">Totale Trasporto</th>
                <th width="9%">Quantità Consegnata</th>
                <th width="9%">Totale Consegnato</th>
            @elseif($order->status != 'archived')
                <th width="25%">Prodotto</th>
                <th width="15%">Unità di Misura</th>
                <th width="15%">Quantità Ordinata</th>
                <th width="15%">Totale Trasporto</th>
                <th width="15%">Quantità Consegnata</th>
                <th width="15%">Totale Consegnato</th>
            @else
                <th width="25%">Prodotto</th>
                <th width="15%">Unità di Misura</th>
                <th width="20%">Quantità Ordinata</th>
                <th width="20%">Quantità Consegnata</th>
                <th width="20%">Totale Consegnato</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach($order->supplier->products as $product)
            @if($order->hasProduct($product))
                <tr data-product-id="{{ $product->id }}">
                    <!-- Prodotto -->
                    <td>
                        @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])
                    </td>

                    @if($order->isActive())
                        <td>{{ printablePrice($product->price) }} €</td>
                        <td>{{ printablePrice($product->transport) }} €</td>
                        <td>{{ printableQuantity($product->max_available, $product->measure->discrete) }}</td>
                    @endif

                    <!-- Unità di Misura -->
                    <td>{{ $product->printableMeasure(true) }}</td>

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
                            <label class="order-summary-product-price">{{ $summary->products[$product->id]['price'] }} €</label>
                        </td>
                    @endif

                    @if($order->status != 'archived')
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
                </tr>
            @endif
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
                <th class="order-summary-order-price">{{ printablePrice($summary->price) }} €</th>
                <th class="order-summary-order-transport">{{ printablePrice($summary->transport) }} €</th>
                <th></th>
                <th>
                    <span class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} €</span>
                    @if($summary->transport_delivered)
                        +<br/><span class="order-summary-order-transport_delivered">{{ printablePrice($summary->transport_delivered) }} €</span>
                    @endif
                </th>
            @else
                <th></th>
                <th></th>
                <th></th>

                @if($order->status != 'archived')
                    <th class="order-summary-order-transport">{{ printablePrice($summary->transport) }} €</th>
                @endif

                <th></th>
                <th>
                    <span class="order-summary-order-price_delivered">{{ printablePrice($summary->price_delivered) }} €</span>
                    @if($summary->transport_delivered)
                        +<br/><span class="order-summary-order-transport_delivered">{{ printablePrice($summary->transport_delivered) }} €</span>
                    @endif
                </th>
            @endif
        </tr>
    </thead>
</table>
