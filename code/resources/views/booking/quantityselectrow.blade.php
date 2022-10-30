<?php

if(!isset($while_shipping)) {
    $while_shipping = false;
}

$booked_quantity = (isset($o) ? $o->getBookedQuantity($product) : 0);

?>

@if($product->variants->isEmpty() == false)
    <input type="hidden" name="{{ $product->id }}" value="{{ $populate ? $booked_quantity : '' }}" />

    <div class="variants-selector">
        <?php $booked = isset($o) ? $o->getBooked($product) : null ?>

        @if($booked != null && $booked->variants->count() != 0)
            @foreach($booked->variants as $var)
                @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'while_shipping' => $while_shipping, 'saved' => $var])
            @endforeach
        @else
            @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'while_shipping' => $while_shipping, 'saved' => null])
        @endif

        @if($while_shipping == false)
            @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => true, 'while_shipping' => $while_shipping, 'saved' => null])
        @endif
    </div>
@else
    <div class="input-group booking-product-quantity">
        <input type="text" class="form-control number" name="{{ $product->id }}" value="{{ $populate ? $booked_quantity : '' }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
        <div class="input-group-text">
            @if($while_shipping)
                {{ $product->measure->name }}
            @else
                {{ $product->printableMeasure() }}
            @endif
        </div>
        <div class="invalid-feedback"></div>
    </div>
@endif
