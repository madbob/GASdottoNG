<?php

$more_orders = ($aggregate->orders->count() > 1);
$handling_movements = someoneCan('movements.admin', $currentgas);
$tot_amount = 0;
$tot_delivered = [];
$rand = rand();
$existing = false;

?>

<div class="row">
    <div class="col">
        <form class="form-horizontal inner-form booking-form" method="POST" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}" data-dynamic-url="{{ route('booking.dynamics', ['aggregate_id' => $aggregate->id, 'user_id' => $user->id]) }}" data-reference-modal="editMovement-{{ $rand }}">
            {{--
                Questo valore viene all'occorrenza modificato via JS quando
                viene cliccato il tasto di "Salva Informazioni"
            --}}
            <input type="hidden" name="action" value="shipped">

            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticobjfield', ['target_obj' => $user, 'label' => _i('Prenotato Da')])
                </div>
                <div class="col-md-6">
                    @foreach($user->contacts as $contact)
                        @if($contact->type == 'phone' || $contact->type == 'mobile')
                            <x-larastrap::text :label="$contact->type_name" :value="$contact->value" disabled readonly />
                        @endif
                    @endforeach
                </div>
            </div>

            @foreach($aggregate->orders as $order)
                @if($more_orders)
                    <h4>{{ $order->printableName() }}</h4>
                @endif

                <?php

                    $o = $order->userBooking($user->id);

                    $existing = ($existing || $o->exists || $o->friends_bookings->isEmpty() == false);

                    if ($o->status == 'pending') {
                        $now_delivered = 0;
                        $mods = [];
                    }
                    else if ($o->status == 'saved') {
                        $now_delivered = $o->getValue('effective', true);
                        $mods = $o->applyModifiers(null, false);
                    }
                    else {
                        $now_delivered = $o->getValue('effective', true);
                        $mods = $o->applyModifiers(null, true);
                    }

                    $tot_delivered[$o->id] = $now_delivered;
                    $tot_amount += $now_delivered;

                ?>

                @if($o->status == 'shipped')
                    <div class="row">
                        <div class="col-md-6">
                            @include('commons.staticobjfield', ['obj' => $o, 'name' => 'deliverer', 'label' => _i('Consegnato Da')])
                            <x-larastrap::datepicker :label="_i('Data Consegna')" :value="$o->delivery" disabled readonly />
                        </div>
                        <div class="col-md-6">
                            @include('commons.staticmovementfield', ['obj' => $o->payment, 'label' => _i('Pagamento'), 'rand' => $rand])
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col">
                        <table class="table table-striped booking-editor" data-booking-id="{{ $o->id }}" data-order-id="{{ $order->id }}">
                            <input type="hidden" name="booking_id" value="{{ $o->id }}" class="skip-on-submit">

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
                                                <label class="static-label">{{ $product->product->name }}</label>
                                            </td>

                                            <td>
                                                <label class="static-label booking-product-booked">{{ printableQuantity($product->quantity, $discrete_quantity) }} {{ $product->product->printableMeasure(true) }}</label>
                                            </td>

                                            <td>
                                                <div class="input-group booking-product-quantity">
                                                    <input type="text" class="form-control number" name="{{ $product->product->id }}" value="{{ printableQuantity($product->delivered, $discrete_quantity, 3) }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                                    <div class="input-group-text">{{ $product->product->measure->name }}</div>
                                                    @if($product->product->portion_quantity != 0)
                                                        @include('delivery.calculator', ['pieces' => $product->quantity, 'measure' => $product->product->measure->name])
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <label class="static-label booking-product-price float-end">
                                                    <span>{{ printablePrice($product->getValue('delivered')) }}</span> {{ $currentgas->currency }}
                                                </label>
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
                                                        <div class="input-group-text">{{ $product->product->measure->name }}</div>
                                                        @if($product->product->portion_quantity != 0)
                                                            @include('delivery.calculator', ['pieces' => $var->quantity, 'measure' => $product->product->measure->name])
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <label class="static-label booking-product-price float-end">
                                                        <span>{{ printablePrice($var->deliveredValue()) }}</span> {{ $currentgas->currency }}
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                @foreach($mods as $mod_value)
                                    @include('delivery.modifierrow', [
                                        'mod_value' => $mod_value,
                                        'skip_cells' => 2,
                                        'final_value' => true,
                                    ])
                                @endforeach

                                @include('delivery.modifierrow', [
                                    'mod_value' => null,
                                    'skip_cells' => 2
                                ])

                                @if($order->isActive())
                                    <tr class="hidden booking-product fit-add-product">
                                        <td>
                                            <select class="fit-add-product-select form-select">
                                                <option value="-1">{{ _i('Seleziona un Prodotto') }}</option>
                                                @foreach($order->products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>&nbsp;</td>

                                        <td class="bookable-target">&nbsp;</td>

                                        <td>
                                            <label class="static-label booking-product-price float-end">
                                                <span>0.00</span> {{ $currentgas->currency }}
                                            </label>
                                        </td>
                                    </tr>
                                @endif
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

                                    @if($currentgas->unmanaged_shipping == '1' && $order->supplier->unmanaged_shipping_enabled)
                                        <th class="text-end"><x-larastrap::price :label="_i('Totale Manuale')" :name="sprintf('manual_total_%s', $order->id)" classes="booking-total manual-total" :value="$now_delivered" data-manual-change="0" /></th>
                                    @else
                                        <th class="text-end">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($now_delivered) }}</span> {{ $currentgas->currency }}</th>
                                    @endif
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
                                <div class="float-end">
                                    <strong>{{ _i('Totale Complessivo') }}: <span class="all-bookings-total">{{ printablePrice($tot_amount) }}</span> {{ $currentgas->currency }}</strong>
                                </div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            @endif

            @if($order->isActive())
                <div class="row">
                    <div class="col">
                        <div class="btn-group float-end main-form-buttons" role="group">
                            @if($existing)
                                <button class="btn btn-light preload-quantities">{{ _i('Carica Quantità Prenotate') }}</button>
                                <button type="submit" class="btn btn-info info-button">{{ _i('Salva Informazioni') }}</button>
                            @endif

                            <button type="submit" class="btn btn-success saving-button">{{ _i('Consegna') }}</button>
                        </div>
                    </div>
                </div>
            @endif

            <input type="hidden" name="pre-saved-function" value="submitDeliveryForm">
            <input type="hidden" name="reload-portion" value=".order-summary-wrapper" class="skip-on-submit" />

            @if($handling_movements)
                <input type="hidden" name="post-saved-function" value="triggerPayment">
            @endif

            @if($existing)
                <input type="hidden" name="post-saved-function" value="closeMainForm">
            @else
                <input type="hidden" name="append-list" value="booking-list-{{ $aggregate->id }}">
                <input type="hidden" name="close-modal" value="1">
            @endif
        </form>
    </div>

    @if($handling_movements)
        @include('movement.modal', [
            'dom_id' => $rand,
            'obj' => null, // qui gestisco sempre un movimento di pagamento come nuovo, eventualmente la pre-callback di 'booking-payment' provvederà a caricare quelli esistenti assegnati alle prenotazioni contemplate
            'default' => \App\Movement::generate('booking-payment', $user, $aggregate, $tot_amount),
            'amount_label' => _i('Importo da Pagare'),
            'extra' => [
                'delivering-status' => json_encode($tot_delivered)
            ],
        ])
    @endif

    @stack('postponed')
</div>
