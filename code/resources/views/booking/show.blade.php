<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;

?>

@include('booking.head', ['aggregate' => $aggregate])

<form class="form-horizontal main-form">
    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php $o = $order->userBooking($user->id) ?>

        @if($o->products->isEmpty())
            <div class="alert alert-info">
                {{ _i("Non hai partecipato a quest'ordine.") }}
            </div>
            <br/>
        @else
            <table class="table table-striped booking-editor">
                <thead>
                    <tr>
                        <th width="50%">{{ _i('Prodotto') }}</th>
                        <th width="15%">{{ _i('Ordinato') }}</th>
                        <th width="15%">{{ _i('Consegnato') }}</th>
                        <th width="10%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="10%" class="text-right">{{ _i('Totale Prezzo') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($o->products as $product)
                        @if($product->variants->isEmpty() == true)
                            <tr>
                                <td>
                                    @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product->product])
                                </td>

                                <td>
                                    {{ printableQuantity($product->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                                </td>

                                <td>
                                    {{ printableQuantity($product->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                                </td>

                                <td>
                                    <label class="static-label">
                                        {!! $product->product->printablePrice($order) !!}
                                    </label>
                                </td>

                                <td>
                                    <label class="static-label booking-product-price pull-right">
                                        {{ printablePriceCurrency($o->status == 'shipped' ? $product->final_price : $product->quantityValue()) }}
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

                                    <td>
                                        <label class="static-label">
                                            {!! $product->product->printablePrice($order) !!}
                                        </label>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-price pull-right">
                                            {{ printablePriceCurrency($o->status == 'shipped' ? $var->final_price : $var->quantityValue()) }}
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    <tr class="booking-transport">
                        <td>
                            <label class="static-label">{{ _i('Trasporto') }}</label>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>
                            <input type="hidden" name="global-transport-price" value="{{ $o->major_transport }}" class="skip-on-submit" />
                            <label class="static-label booking-transport-price pull-right"><span>{{ printablePrice($o->check_transport) }}</span> {{ $currentgas->currency }}</label>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-right">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($o->total_value) }}</span> {{ $currentgas->currency }}</th>
                    </tr>
                </tfoot>
            </table>

            @if(!empty($o->notes))
                <div class="row">
                    <div class="col-md-12">
                        @include('commons.staticstringfield', ['obj' => $o, 'name' => 'notes', 'label' => _i('Note')])
                    </div>
                </div>
            @endif

            <?php $grand_total += $o->value ?>
        @endif
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="pull-right">
                            <strong>{{ _i('Totale Complessivo') }}: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="row">
        <div class="col-md-12">
            @include('booking.friendsbuttons', ['aggregate' => $aggregate, 'user' => $user, 'mode' => 'show'])

            <div class="btn-group pull-right main-form-buttons" role="group">
                <button type="button" class="btn btn-default close-button">{{ _i('Chiudi') }}</button>
            </div>
        </div>
    </div>
</form>
