<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;

?>

<form class="form-horizontal main-form">
    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php $o = $order->userBooking($user->id) ?>

        @if($o->products->isEmpty())
            <div class="alert alert-info">
                Non hai partecipato a quest'ordine.
            </div>
            <br/>
        @else
            <table class="table table-striped booking-editor">
                <thead>
                    <tr>
                        <th width="30%">Prodotto</th>
                        <th width="20%">Ordinato</th>
                        <th width="20%">Consegnato</th>
                        <th width="20%">Prezzo Unitario</th>
                        <th width="10%">Prezzo Totale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($o->products as $product)
                        @if($product->variants->isEmpty() == true)
                            <tr>
                                <td>
                                    <label class="static-label">
                                        {{ $product->product->name }}

                                        @if(!empty($product->description))
                                            <button type="button" class="btn btn-xs btn-default" data-container="body" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="{{ str_replace('"', '\"', $product->description) }}">
                                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                    </label>
                                </td>

                                <td>
                                    {{ printableQuantity($product->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                                </td>

                                <td>
                                    {{ printableQuantity($product->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                                </td>

                                <td class="text-right">
                                    <label class="static-label">{!! $product->product->printablePrice($order) !!}</label>
                                </td>

                                <td>
                                    <label class="static-label booking-product-price pull-right">
                                        {{ printablePrice($o->status == 'shipped' ? $product->final_price : $product->quantityValue()) }} €
                                    </label>
                                </td>
                            </tr>
                        @else
                            @foreach($product->variants as $var)
                                <tr>
                                    <td>
                                        <label class="static-label">
                                            {{ $product->product->name }}: {{ $var->printableName() }}

                                            @if(!empty($product->description))
                                                <button type="button" class="btn btn-xs btn-default" data-container="body" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="{{ str_replace('"', '\"', $product->description) }}">
                                                    <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                                </button>
                                            @endif
                                        </label>
                                    </td>

                                    <td>
                                        {{ printableQuantity($var->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                                    </td>

                                    <td>
                                        {{ printableQuantity($var->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                                    </td>

                                    <td class="text-right">
                                        <label class="static-label">{!! $product->product->printablePrice($order) !!}</label>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-price pull-right">
                                            {{ printablePrice($o->status == 'shipped' ? $var->final_price : $var->quantityValue()) }} €
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-right">Totale: <span class="booking-total">{{ printablePrice($o->value) }}</span> €</th>
                    </tr>
                </tfoot>
            </table>

            <?php $grand_total += $o->value ?>
        @endif
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="pull-right">
                            <strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> €</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
                <button type="button" class="btn btn-default close-button">Chiudi</button>
            </div>
        </div>
    </div>
</form>
