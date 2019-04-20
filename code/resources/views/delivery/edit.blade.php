<?php

$more_orders = ($aggregate->orders->count() > 1);
$handling_movements = App\Role::someone('movements.admin', $currentgas);
$tot_amount = 0;
$tot_delivered = [];
$rand = rand();
$existing = false;

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}" data-reference-modal="editMovement-{{ $rand }}">
    <input type="hidden" name="action" value="shipped">

    <div class="well">
        <div class="row">
            <div class="col-md-6">
                @include('commons.staticobjfield', ['target_obj' => $user, 'label' => 'Prenotato Da'])
            </div>
            <div class="col-md-6">
                @foreach($user->contacts as $contact)
                    @if($contact->type == 'phone' || $contact->type == 'mobile')
                        @include('commons.staticstringfield', ['obj' => $contact, 'name' => 'value', 'label' => $contact->type_name])
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h4>{{ $order->printableName() }}</h4>
        @endif

        <?php

            $o = $order->userBooking($user->id);
            $existing = ($existing || $o->exists || $o->friends_bookings->isEmpty() == false);

            if ($o->status == 'pending')
                $now_delivered = 0;
            else
                $now_delivered = $o->getValue('delivered', true) + $o->getValue('transport', true) - $o->getValue('discount', true);

            $tot_delivered[$o->id] = $now_delivered;
            $tot_amount += $now_delivered;

        ?>

        @if($o->status == 'shipped')
            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticobjfield', ['obj' => $o, 'name' => 'deliverer', 'label' => 'Consegnato Da'])
                    @include('commons.staticdatefield', ['obj' => $o, 'name' => 'delivery', 'label' => 'Data Consegna'])
                </div>
                <div class="col-md-6">
                    @include('commons.staticmovementfield', ['obj' => $o->payment, 'label' => 'Pagamento', 'rand' => $rand])
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped booking-editor" data-booking-id="{{ $o->id }}" data-order-id="{{ $order->id }}">
                    <thead>
                        <tr>
                            <th width="25%"></th>
                            <th width="25%"></th>
                            <th width="25%"></th>
                            <th width="25%"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($o->products_with_friends as $product)
                            <?php $discrete_quantity = $product->product->measure->discrete ?>

                            @if($product->variants->isEmpty() == true)
                                <tr class="booking-product">
                                    <td>
                                        <input type="hidden" name="booking-product-real-booked" value="{{ printableQuantity($product->true_quantity, $discrete_quantity) }}" class="skip-on-submit" />
                                        <input type="hidden" name="product-price" value="{{ $product->product->contextualPrice($order, false) }}" class="skip-on-submit" />
                                        <input type="hidden" name="product-transport" value="{{ $product->product->transport }}" class="skip-on-submit" />
                                        <label class="static-label">{{ $product->product->name }}</label>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-booked">{{ printableQuantity($product->quantity, $discrete_quantity) }} {{ $product->product->printableMeasure(true) }}</label>
                                    </td>

                                    <td>
                                        <div class="input-group booking-product-quantity">
                                            <input type="text" class="form-control number" name="{{ $product->product->id }}" value="{{ printableQuantity($product->delivered, $discrete_quantity, 3) }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                            <div class="input-group-addon">{{ $product->product->measure->name }}</div>
                                            @if($product->product->portion_quantity != 0)
                                                @include('delivery.calculator', ['pieces' => $product->quantity, 'measure' => $product->product->measure->name])
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-price pull-right">{{ printablePriceCurrency($product->final_price) }}</label>
                                    </td>
                                </tr>
                            @else
                                <?php $base_price = $product->product->contextualPrice($order, false) ?>

                                @foreach($product->variants as $var)
                                    <?php

                                        $price = $base_price;
                                        foreach ($var->components as $comp) {
                                            $price += $comp->value->price_offset;
                                        }

                                    ?>

                                    <tr class="booking-product">
                                        <td>
                                            <input type="hidden" name="booking-product-real-booked" value="{{ printableQuantity($var->true_quantity, $discrete_quantity) }}" class="skip-on-submit" />
                                            <input type="hidden" name="product-price" value="{{ $price }}" class="skip-on-submit" />
                                            <input type="hidden" name="product-transport" value="{{ $product->product->transport }}" class="skip-on-submit" />

                                            <label class="static-label">{{ $product->product->name }}: {{ $var->printableName() }}</label>

                                            <input type="hidden" name="{{ $product->product->id }}" value="1" />
                                            @foreach($var->components as $comp)
                                                <input type="hidden" name="variant_selection_{{ $comp->variant->id }}[]" value="{{ $comp->value->id }}" />
                                            @endforeach
                                        </td>

                                        <td>
                                            <label class="static-label booking-product-booked">{{ printableQuantity($var->quantity, $discrete_quantity) }} {{ $product->product->printableMeasure(true) }}</label>
                                        </td>

                                        <td>
                                            <div class="input-group booking-product-quantity">
                                                <input type="text" class="form-control number" name="variant_quantity_{{ $product->product->id }}[]" value="{{ printableQuantity($var->delivered, $discrete_quantity, 3) }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                                <div class="input-group-addon">{{ $product->product->measure->name }}</div>
                                                @if($product->product->portion_quantity != 0)
                                                    @include('delivery.calculator', ['pieces' => $var->quantity, 'measure' => $product->product->measure->name])
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <label class="static-label booking-product-price pull-right">{{ printablePriceCurrency($var->final_price) }}</label>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach

                        @if($order->isActive())
                            <tr class="hidden booking-product fit-add-product">
                                <td>
                                    <select class="fit-add-product-select form-control">
                                        <option value="-1">{{ _i('Seleziona un Prodotto') }}</option>
                                        @foreach($order->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>&nbsp;</td>

                                <td class="bookable-target">&nbsp;</td>

                                <td>
                                    <label class="static-label booking-product-price pull-right">0.00 {{ $currentgas->currency }}</label>
                                </td>
                            </tr>
                        @endif

                        @if(($transport = $o->getValue('transport', true)) != 0)
                            <tr class="booking-transport">
                                <td>
                                    <label class="static-label">{{ _i('Trasporto') }}</label>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <input type="hidden" name="global-transport-price" value="{{ $transport }}" class="skip-on-submit" />
                                    <label class="static-label booking-transport-price pull-right">
                                        <span>{{ printablePrice($transport) }}</span> {{ $currentgas->currency }}
                                    </label>
                                </td>
                            </tr>
                        @endif

                        <tr class="booking-discount">
                            <td>
                                <label class="static-label">{{ _i('Sconto') }} {{ printablePercentage($order->discount) }}</label>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <input type="hidden" name="global-discount-value" value="{{ $order->discount }}">
                                <label class="static-label booking-discount-value pull-right">
                                    <span>{{ printablePrice($o->status == 'pending' ? 0 : $o->getValue('discount', true)) }}</span> {{ $currentgas->currency }}
                                </label>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>
                                @if($order->isActive())
                                    <button class="btn btn-warning add-booking-product">{{ _i('Aggiungi Prodotto') }}</button>
                                @endif
                            </th>
                            <th></th>
                            <th></th>
                            <th class="text-right">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($now_delivered) }}</span> {{ $currentgas->currency }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="pull-right">
                            <strong>{{ _i('Totale Complessivo') }}: <span class="all-bookings-total">{{ printablePrice($tot_amount) }}</span> {{ $currentgas->currency }}</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    @if($order->isActive())
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right main-form-buttons" role="group">
                    @if($existing)
                        <button class="btn btn-default preload-quantities">{{ _i('Carica Quantità Prenotate') }}</button>
                        <button type="submit" class="btn btn-info info-button">{{ _i('Salva Informazioni') }}</button>
                    @endif

                    @if($handling_movements)
                        <button type="button" class="btn btn-success saving-button" data-toggle="modal" data-target="#editMovement-{{ $rand }}">{{ _i('Consegna') }}</button>
                    @else
                        <button type="submit" class="btn btn-success saving-button">{{ _i('Consegna') }}</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <input type="hidden" name="post-saved-function" value="updateOrderSummary">

    @if($existing)
        <input type="hidden" name="post-saved-function" value="closeMainForm">
    @else
        <input type="hidden" name="append-list" value="booking-list-{{ $aggregate->id }}">
    @endif
</form>

@if($handling_movements)
    @include('movement.modal', [
        'dom_id' => $rand,
        'obj' => null, // qui gestisco sempre un movimento di pagamento come nuovo, eventualmente la pre-callback di 'booking-payment' provvederà a caricare quelli esistenti assegnati alle prenotazioni contemplate
        'default' => \App\Movement::generate('booking-payment', $user, $aggregate, $tot_amount),
        'amount_label' => _i('Importo da Pagare'),
        'extra' => [
            'post-saved-function' => 'submitDeliveryForm',
            'delivering-status' => json_encode($tot_delivered)
        ],
    ])
@endif
