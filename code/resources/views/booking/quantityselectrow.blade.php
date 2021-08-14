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
                @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'saved' => $var])
            @endforeach
        @else
            @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => false, 'saved' => null])
        @endif

        @include('booking.variantselectrow', ['product' => $product, 'order' => $order, 'master' => true, 'saved' => null])
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
    </div>
    <div class="mobile-quantity-switch d-inline-block d-md-none float-end">
        <button class="btn btn-light plus"><i class="bi-plus"></i></button>
        <button class="btn btn-light minus"><i class="bi-dash"></i></button>
    </div>
@endif
