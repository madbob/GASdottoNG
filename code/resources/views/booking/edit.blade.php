<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();

?>

@include('booking.head', ['aggregate' => $aggregate])

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('booking/' . $aggregate->id . '/user/' . $user->id) }}">
    <input type="hidden" name="post-saved-function" value="afterBookingSaved">

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php

        $notice = null;

        $o = $order->userBooking($user->id);
        if ($currentgas->pending_packages_enabled) {
            if ($order->status == 'open') {
                $products = $order->products;
            }
            else {
                $products = $order->pendingPackages();
                $notice = _i("Attenzione: quest'ordine è chiuso, ma è possibile prenotare ancora alcuni prodotti per completare le confezioni da consegnare.");
            }
        }
        else {
            $products = $order->products;
        }

        ?>

        @if(!is_null($notice))
            <div class="alert alert-info">
                {{ $notice }}
            </div>
            <br>
        @endif

        @include('commons.iconslegend', [
            'class' => 'Product',
            'target' => '#booking_' . sanitizeId($order->id),
            'table_filter' => true,
            'limit_to' => ['th'],
            'contents' => $products
        ])

        <table class="table table-striped booking-editor" id="booking_{{ sanitizeId($order->id) }}">
            <thead>
                <tr>
                    <th width="40%"></th>
                    <th width="30%"></th>
                    <th width="15%"></th>
                    <th width="10%"></th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <?php $p = $o->getBooked($product->id) ?>

                    <tr class="booking-product">
                        <td>
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])

                            <div class="hidden">
                                @foreach($product->icons() as $icon)
                                    <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>
                                @endforeach
                            </div>
                        </td>

                        <td>
                            @include('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => true])
                        </td>

                        <td>
                            <label class="static-label"><small>{{ $product->printableDetails($order) }}</small></label>
                        </td>

                        <td class="text-right">
                            <label class="static-label"><small>{!! $product->printablePrice($order) !!}</small></label>
                        </td>

                        <td>
                            <label class="static-label booking-product-price pull-right">{{ printablePriceCurrency($p ? $p->quantityValue() : 0) }}</label>
                            <input type="hidden" name="product-transport" value="{{ $product->transport }}" class="skip-on-submit" />
                            <input type="hidden" name="product-discount" value="{{ $product->discount }}" class="skip-on-submit" />
                        </td>
                    </tr>
                @endforeach

                @include('delivery.dynamicrow', [
                    'identifier' => 'transport',
                    'label' => _i('Trasporto'),
                    'skip_cells' => 3
                ])

                @include('delivery.dynamicrow', [
                    'identifier' => 'discount',
                    'label' => _i('Sconto'),
                    'skip_cells' => 3
                ])
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-right">Totale: <span class="booking-total">{{ printablePrice($o->getValue('effective', false)) }}</span> {{ $currentgas->currency }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="row">
            <div class="col-md-12">
                @include('commons.textarea', ['obj' => $o, 'name' => 'notes', 'postfix' => '_' . $order->id, 'label' => _i('Note')])
            </div>
        </div>

        <?php $grand_total += $o->getValue('effective', false) ?>
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="pull-right">
                            <strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group">
                <button type="button" class="btn btn-danger delete-booking">{{ _i('Annulla Prenotazione') }}</button>
                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>
