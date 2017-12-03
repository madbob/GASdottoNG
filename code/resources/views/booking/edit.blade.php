<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('booking/' . $aggregate->id . '/user/' . $user->id) }}">
    <input type="hidden" name="post-saved-function" value="afterBookingSaved">

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php $o = $order->userBooking($user->id) ?>

        <table class="table table-striped booking-editor">
            <thead>
                <tr>
                    <th width="25%"></th>
                    <th width="30%"></th>
                    <th width="25%"></th>
                    <th width="15%"></th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->products as $product)
                    <?php $p = $o->getBooked($product->id) ?>

                    <tr class="booking-product">
                        <td>
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product])
                        </td>

                        <td>
                            @include('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => true])
                        </td>

                        <td>
                            <label class="static-label"><small>{{ $product->printableDetails($order) }}</small></label>
                        </td>

                        <td class="text-right">
                            <label class="static-label">{!! $product->printablePrice($order) !!}</label>
                        </td>

                        <td>
                            <label class="static-label booking-product-price pull-right">{{ $p ? printablePrice($p->quantityValue()) : '0.00' }} €</label>
                        </td>
                    </tr>
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
                <button type="button" class="btn btn-danger delete-booking">{{ _i('Annulla Prenotazione') }}</button>
                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>
