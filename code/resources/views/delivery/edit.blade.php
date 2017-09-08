<?php

$more_orders = ($aggregate->orders->count() > 1);

/*
    Se il GAS ha abilitato il pagamento RID, ed esiste la configurazione per
    l'utente, non viene chiesto il pagamento della consegna (assumendo che
    avverrà appunto via RID)
*/
$handling_movements = App\Role::someone('movements.admin', $currentgas) && (empty($currentgas->rid_name) || empty($user->iban));

$tot_amount = 0;
$tot_delivered = [];
$rand = rand();

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}" data-reference-modal="editMovement-{{ $rand }}">
    <input type="hidden" name="action" value="shipped">

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php

            $o = $order->userBooking($user->id);
            $now_delivered = $o->total_delivered;
            $tot_delivered[$o->id] = $now_delivered;
            $tot_amount += $now_delivered;

        ?>

        <div class="row">
            <div class="col-md-6">
                @include('commons.staticobjfield', ['obj' => $o, 'name' => 'deliverer', 'label' => 'Consegnato Da'])
                @include('commons.staticdatefield', ['obj' => $o, 'name' => 'delivery', 'label' => 'Data Consegna'])
            </div>
            <div class="col-md-6">
                @include('commons.staticmovementfield', ['obj' => $o->payment, 'label' => 'Pagamento', 'rand' => $rand])
            </div>
        </div>

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
                        @foreach($o->products as $product)
                            @if($product->variants->isEmpty() == true)
                                <tr class="booking-product">
                                    <td>
                                        <input type="hidden" name="product-price" value="{{ $product->product->contextualPrice($order, false) + $product->product->transport }}" class="skip-on-submit" />
                                        <label class="static-label">{{ $product->product->name }}</label>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-booked">{{ printableQuantity($product->true_quantity) }}</label>
                                    </td>

                                    <td>
                                        <div class="input-group booking-product-quantity">
                                            <input type="number" step="any" min="0" class="form-control trim-2-ddigits" name="{{ $product->product->id }}" value="{{ $product->delivered }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                            <div class="input-group-addon">{{ $product->product->measure->name }}</div>
                                            @if($product->product->portion_quantity != 0)
                                                @include('delivery.calculator', ['pieces' => $product->quantity, 'measure' => $product->product->measure->name])
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <label class="static-label booking-product-price pull-right">{{ printablePrice($product->deliveredValue()) }} €</label>
                                    </td>
                                </tr>
                            @else
                                <?php $base_price = $product->product->contextualPrice($order, false) + $product->product->transport ?>

                                @foreach($product->variants as $var)
                                    <?php

                                        $price = $base_price;
                                        foreach ($var->components as $comp) {
                                            $price += $comp->value->price_offset;
                                        }

                                    ?>

                                    <tr class="booking-product">
                                        <td>
                                            <input type="hidden" name="product-price" value="{{ $price }}" class="skip-on-submit" />

                                            <label class="static-label">{{ $product->product->name }}: {{ $var->printableName() }}</label>

                                            <input type="hidden" name="{{ $product->product->id }}" value="1" />
                                            @foreach($var->components as $comp)
                                                <input type="hidden" name="variant_selection_{{ $comp->variant->id }}[]" value="{{ $comp->value->id }}" />
                                            @endforeach
                                        </td>

                                        <td>
                                            <label class="static-label booking-product-booked">{{ printableQuantity($var->true_quantity) }}</label>
                                        </td>

                                        <td>
                                            <div class="input-group booking-product-quantity">
                                                <input type="number" step="any" min="0" class="form-control" name="variant_quantity_{{ $product->product->id }}[]" value="{{ $var->delivered }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                                <div class="input-group-addon">{{ $product->product->measure->name }}</div>
                                                @if($product->product->portion_quantity != 0)
                                                    @include('delivery.calculator', ['pieces' => $var->quantity, 'measure' => $product->product->measure->name])
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <label class="static-label booking-product-price pull-right">{{ printablePrice($var->deliveredValue()) }} €</label>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach

                        @if($order->isActive())
                            <tr class="hidden booking-product fit-add-product">
                                <td>
                                    <select class="fit-add-product-select form-control">
                                        <option value="-1">Seleziona un Prodotto</option>
                                        @foreach($order->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="bookable-target">&nbsp;</td>
                            </tr>
                        @endif

                        @if($o->transport != 0)
                            <tr class="booking-transport">
                                <td>
                                    <label class="static-label">Trasporto</label>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <span>{{ printablePrice($o->transport) }}</span> €
                                </td>
                            </tr>
                        @endif
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>
                                @if($order->isActive())
                                    <button class="btn btn-default add-booking-product">Aggiungi Prodotto</button>
                                @endif
                            </th>
                            <th></th>
                            <th></th>
                            <th class="text-right">Totale: <span class="booking-total">{{ printablePrice($now_delivered) }}</span> €</th>
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
                            <strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($tot_amount) }}</span> €</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    @if($order->isActive())
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
                    <button class="btn btn-default preload-quantities">Carica Quantità Prenotate</button>
                    <button type="submit" class="btn btn-info info-button">Salva Informazioni</button>

                    @if($handling_movements)
                        <button type="button" class="btn btn-success saving-button" data-toggle="modal" data-target="#editMovement-{{ $rand }}">Consegna</button>
                    @else
                        <button type="submit" class="btn btn-success saving-button">Consegna</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <input type="hidden" name="post-saved-function" value="updateOrderSummary">
    <input type="hidden" name="post-saved-function" value="closeMainForm">
</form>

@if($handling_movements)
    @include('movement.modal', [
        'dom_id' => $rand,
        'obj' => null, // qui gestisco sempre un movimento di pagamento come nuovo, eventualmente la pre-callback di 'booking-payment' provvederà a caricare quelli esistenti assegnati alle prenotazioni contemplate
        'default' => \App\Movement::generate('booking-payment', $user, $aggregate, $tot_amount),
        'extra' => [
            'post-saved-function' => 'submitDeliveryForm',
            'delivering-status' => json_encode($tot_delivered)
        ],
    ])
@endif
