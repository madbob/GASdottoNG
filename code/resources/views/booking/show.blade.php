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

        <?php

        $contacts = $order->showableContacts();
        $o = $order->userBooking($user->id);
        $mods = $o->applyModifiers(null, false);

        ?>

        @if($contacts->isEmpty() == false)
            <div class="alert alert-info">
                {{ _i('Per segnalazioni relative a questo ordine si può contattare:') }}
                <ul>
                    @foreach($contacts as $contact)
                        <li>{{ $contact->printableName() }} - {{ join(', ', $contact->formattedFields(['email', 'phone', 'mobile'])) }}</li>
                    @endforeach
                </ul>
            </div>
            <br>
        @endif

        @if($o->products->isEmpty())
            <div class="alert alert-info">
                {{ _i("Non hai partecipato a quest'ordine.") }}
            </div>
            <br/>
        @else
            @include('commons.iconslegend', [
                'class' => 'Product',
                'target' => '#booking_' . sanitizeId($order->id),
                'table_filter' => true,
                'limit_to' => ['th'],
                'contents' => $order->products
            ])

            <table class="table table-striped booking-editor" id="booking_{{ sanitizeId($order->id) }}">
                <thead>
                    <tr>
                        <th width="50%">{{ _i('Prodotto') }}</th>
                        <th width="20%">{{ _i('Ordinato') }}</th>
                        <th width="20%">{{ _i('Consegnato') }}</th>
                        <th width="10%" class="text-right">{{ _i('Totale Prezzo') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($o->products as $product)
                        @if($product->variants->isEmpty() == true)
                            <tr>
                                <td>
                                    @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product->product])

                                    <div class="hidden">
                                        @foreach($product->product->icons() as $icon)
                                            <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>
                                        @endforeach
                                    </div>
                                </td>

                                <td>
                                    {{ printableQuantity($product->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                                </td>

                                <td>
                                    {{ printableQuantity($product->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                                </td>

                                <td>
                                    <label class="pull-right">
                                        {{ printablePriceCurrency($product->getValue('effective')) }}
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
                                        <label class="pull-right">
                                            {{ printablePriceCurrency($o->status == 'shipped' ? $var->final_price : $var->quantityValue()) }}
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
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-right">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($o->getValue('effective', false)) }}</span> {{ $currentgas->currency }}</th>
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

            <?php $grand_total += $o->getValue('effective', false) ?>
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
            <div class="btn-group pull-right main-form-buttons" role="group">
                <button type="button" class="btn btn-default close-button">{{ _i('Chiudi') }}</button>
            </div>
        </div>
    </div>
</form>
