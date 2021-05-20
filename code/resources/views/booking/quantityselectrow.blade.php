<?php

if(!isset($while_shipping))
    $while_shipping = false;

$booked_quantity = (isset($o) ? $o->getBookedQuantity($product) : 0);

?>

@if($product->variants->isEmpty() == false)
    <input type="hidden" name="{{ $product->id }}" value="1" />

    <div class="variants-selector">
        @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => true, 'saved' => null])

        <?php $booked = isset($o) ? $o->getBooked($product) : null ?>
        @if($booked != null)
            @foreach($booked->variants as $var)
                @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'saved' => $var])
            @endforeach
        @else
            @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'saved' => null])
        @endif
    </div>
@else
    <div class="input-group booking-product-quantity">
        <input type="text" class="form-control number" name="{{ $product->id }}" value="{{ $populate ? $booked_quantity : '' }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
        <div class="input-group-addon">
            @if($while_shipping)
                {{ $product->measure->name }}
            @else
                {{ $product->printableMeasure() }}
            @endif
        </div>
    </div>
    <div class="mobile-quantity-switch visible-sm-inline-block visible-xs-inline-block pull-right">
        <button class="btn btn-default plus"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
        <button class="btn btn-default minus"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
    </div>
@endif
