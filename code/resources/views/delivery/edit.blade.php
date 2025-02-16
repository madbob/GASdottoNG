<?php

$more_orders = ($aggregate->orders->count() > 1);
$handling_movements = someoneCan('movements.admin', $currentgas);
$tot_amount = 0;
$tot_delivered = [];
$rand = rand();
$existing = false;
$master_summary = $aggregate->reduxData();
$currency_symbol = defaultCurrency()->symbol;

$other_bookings = $user->morePendingBookings($aggregate);

/*
    In fase di consegna, aggrego sempre tutte le quantità
*/
app()->make('AggregationSwitch')->setEnforced(true);

?>

<div class="row">
    <div class="col">
        <form class="form-horizontal inner-form booking-form" method="POST" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}" data-dynamic-url="{{ route('booking.dynamics', ['aggregate_id' => $aggregate->id, 'user_id' => $user->id]) }}" data-reference-modal="editMovement-{{ $rand }}">
            {{--
                Questo valore viene all'occorrenza modificato via JS quando
                viene cliccato il tasto di "Salva Informazioni"
            --}}
            <input type="hidden" name="action" value="shipped">

            <input type="hidden" name="pre-saved-function" value="evaluateEmptyBooking" class="skip-on-submit">

            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticobjfield', ['target_obj' => $user, 'label' => _i('Prenotato Da')])
                </div>
                <div class="col-md-6">
                    @foreach($user->contacts as $contact)
                        @if($contact->type == 'phone' || $contact->type == 'mobile')
                            <x-larastrap::text :label="$contact->type_name" :value="$contact->value" :margins="[0,0,0,0]" disabled readonly />
                        @endif
                    @endforeach
                </div>
            </div>

            @if($other_bookings)
                <div class="row mt-1">
                    <div class="col">
                        <div class="alert alert-info">
                            {!! $other_bookings !!}
                        </div>
                    </div>
                </div>
            @endif

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
                    else {
                        $now_delivered = $o->getValue('effective', true);

                        if ($o->status == 'saved') {
                            $mods = $o->applyModifiersWithFriends($master_summary, false);
                        }
                        else {
                            $mods = $o->applyModifiersWithFriends($master_summary, true);
                        }
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

                @if($order->status == 'closed')
                    <?php

                    $no_price_differences = true;

                    foreach($order->products as $prod) {
                        $base_prod = App\Product::withTrashed()->find($prod->id);
                        $no_price_differences = $base_prod->comparePrices($prod);
                        if ($no_price_differences == false) {
                            break;
                        }
                    }

                    ?>

                    @if($no_price_differences == false)
                        <div class="row">
                            <div class="col">
                                <x-larastrap::suggestion>
                                    {!! _i('I prezzi di alcuni prodotti sono cambiati rispetto alla prenotazione. Sotto, puoi scegliere quale prezzo adottare in caso di rettifica della consegna: quello applicato originariamente o quello nel listino attuale.') !!}
                                </x-larastrap::suggestion>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="row">
                    <div class="col">
                        <table class="table table-striped booking-editor" data-booking-id="{{ $o->id }}" data-order-id="{{ $order->id }}">
                            <input type="hidden" name="booking_id" value="{{ $o->id }}" class="skip-on-submit">

                            <thead>
                                <tr>
                                    <th scope="col" width="30%"></th>
                                    <th scope="col" width="10%"></th>
                                    <th scope="col" width="20%"></th>
                                    <th scope="col" width="25%"></th>
                                    <th scope="col" width="15%"></th>
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
                                                @include('delivery.selectprice', [
                                                    'booking' => $o,
                                                    'product' => $product,
                                                    'combo' => null,
                                                ])
                                            </td>

                                            <td>
                                                <label class="static-label booking-product-booked">{{ printableQuantity($product->quantity, $discrete_quantity) }} {{ $product->product->printableMeasure(true) }}</label>
                                            </td>

                                            <td>
                                                <div class="input-group booking-product-quantity">
                                                    <input type="text" class="form-control number" name="{{ $product->product->id }}" value="{{ printableQuantity($product->delivered, $discrete_quantity, 3) }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
                                                    <div class="input-group-text">{{ $product->product->measure->name }}</div>
                                                    @if($order->isActive() && $product->product->portion_quantity != 0)
                                                        @include('delivery.calculator', ['pieces' => $product->quantity, 'measure' => $product->product->measure->name])
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <label class="static-label booking-product-price float-end">
                                                    <span>{{ printablePrice($product->getValue('delivered')) }}</span> {{ $currency_symbol }}
                                                </label>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($product->variants as $var)
                                            <?php $combo = $var->variantsCombo() ?>

                                            <tr class="booking-product">
                                                <td>
                                                    <input type="hidden" name="booking-product-real-booked" value="{{ printableQuantity($var->true_quantity, $discrete_quantity) }}" class="skip-on-submit" />
                                                    @if($combo)
                                                        <label class="static-label">{{ $combo->printableName() }}</label>
                                                    @else
                                                        <label class="static-label">
                                                            {{ $product->product->printableName() }}<br>
                                                            <small>{{ _i('Nota bene: la variante selezionata in prenotazione non è più nel listino') }}</small>
                                                        </label>
                                                    @endif

                                                    <input type="hidden" name="{{ $product->product->id }}" value="1" />
                                                    @foreach($var->components as $comp)
                                                        <input type="hidden" name="variant_selection_{{ $comp->variant->id }}[]" value="{{ $comp->value->id }}" />
                                                    @endforeach
                                                </td>

                                                <td>
                                                    @include('delivery.selectprice', [
                                                        'booking' => $o,
                                                        'product' => $product,
                                                        'combo' => $combo,
                                                    ])
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
                                                        <span>{{ printablePrice($var->deliveredValue()) }}</span> {{ $currency_symbol }}
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                @foreach($mods as $mod_value)
                                    @include('delivery.modifierrow', [
                                        'mod_value' => $mod_value,
                                        'skip_cells' => 3,
                                        'final_value' => true,
                                    ])
                                @endforeach

                                @include('delivery.modifierrow', [
                                    'mod_value' => null,
                                    'skip_cells' => 3
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
                                                <span>0.00</span> {{ $currency_symbol }}
                                            </label>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td>
                                        @if($order->isActive())
                                            <button class="btn btn-warning add-booking-product">{{ _i('Aggiungi Prodotto') }}</button>
                                        @endif
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                    @if($currentgas->unmanaged_shipping == '1' && $order->supplier->unmanaged_shipping_enabled)
                                        <td class="text-end fw-bold"><x-larastrap::price :label="_i('Totale Manuale')" :name="sprintf('manual_total_%s', $order->id)" classes="booking-total manual-total" :value="$now_delivered" data-manual-change="0" /></td>
                                    @else
                                        <td class="text-end fw-bold">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($now_delivered) }}</span> {{ $currency_symbol }}</td>
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
                            <td>
                                <div class="float-end">
                                    <strong>{{ _i('Totale Complessivo') }}: <span class="all-bookings-total">{{ printablePrice($tot_amount) }}</span> {{ $currency_symbol }}</strong>
                                </div>
                            </td>
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
