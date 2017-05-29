<table class="table order-summary">
    <thead>
        <tr>
            @if($order->isActive())
                <th width="5%">Abilita / Disabilita</th>
                <th width="21%">Prodotto</th>
                <th width="5%">Prezzo</th>
                <th width="5%">Trasporto</th>
                <th width="5%">Sconto Prodotto</th>
                <th width="9%">Unità di Misura</th>
                <th width="9%">Quantità Ordinata</th>
                <th width="9%">Totale Prezzo</th>
                <th width="9%">Totale Trasporto</th>
                <th width="9%">Quantità Consegnata</th>
                <th width="9%">Totale Consegnato</th>
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
                    <td>
                        <input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" <?php if($enabled) echo 'checked' ?> />
                    </td>
                @endif

                <td>
                    <input type="hidden" name="productid[]" value="{{ $product->id }}" />
                    <label>{{ $product->printableName() }}</label>
                </td>

                @if($order->isActive())
                    <td class="product-price">
                        <label class="full-price <?php if(!empty($product->discount) && $enabled && $product->pivot->discount_enabled) echo 'hidden' ?>">{{ printablePrice(applyPercentage($product->price, $order->discount)) }} €</label>
                        <label class="product-discount-price <?php if(empty($product->discount) || !$enabled || ($enabled && !$product->pivot->discount_enabled)) echo 'hidden' ?>">{{ printablePrice(applyPercentage($product->discount_price, $order->discount)) }} €</label>
                    </td>
                    <td>
                        <label>{{ printablePrice($product->transport) }} €</label>
                    </td>
                    <td>
                        @if(!empty($product->discount))
                            <input class="discount-toggle" type="checkbox" name="discounted[]" value="{{ $product->id }}" <?php if($enabled && $product->pivot->discount_enabled) echo 'checked' ?> />
                        @endif
                    </td>
                @endif

                <td>
                    <label>{{ $product->measure->printableName() }}</label>
                </td>

                @if($order->isActive())
                    <td>
                        <label class="order-summary-product-quantity">{{ $summary->products[$product->id]['quantity'] }}</label>
                    </td>
                    <td>
                        <label class="order-summary-product-price">{{ printablePrice($summary->products[$product->id]['price']) }} €</label>
                    </td>
                    <td>
                        <label class="order-summary-product-transport">{{ printablePrice($summary->products[$product->id]['transport']) }} €</label>
                    </td>
                @endif

                <td>
                    <label class="order-summary-product-delivered">{{ $summary->products[$product->id]['delivered'] }}</label>
                </td>
                <td>
                    <label class="order-summary-product-price_delivered">{{ printablePrice($summary->products[$product->id]['price_delivered']) }} €</label>
                </td>

                @if($order->isActive())
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
                            <div class="modal fade" id="fix-{{ $random_identifier }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ url('orders/fixes/' . $order->id) }}">
                                            <input type="hidden" name="product" value="{{ $product->id }}" />

                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="fix-{{ $random_identifier }}-label">Modifica Quantità</h4>
                                            </div>
                                            <div class="modal-body">
                                                @if($product->package_size != 0)
                                                    <p>
                                                        Dimensione confezione: {{ $product->package_size }}
                                                    </p>

                                                    <hr/>
                                                @endif

                                                <table class="table table-striped">
                                                    @foreach($order->bookings as $po)
                                                        <tr>
                                                            <td>
                                                                <label>{{ $po->user->printableName() }}</label>
                                                            </td>
                                                            <td>
                                                                <input type="hidden" name="booking[]" value="{{ $po->id }}" />

                                                                <div class="input-group">
                                                                    <input type="number" class="form-control" name="quantity[]" value="{{ $po->getBookedQuantity($product) }}" />
                                                                    <div class="input-group-addon">{{ $product->printableMeasure() }}</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                                <button type="submit" class="btn btn-primary reloader" data-reload-target="#order-list">Salva</button>
                                            </div>
                                        </form>
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
