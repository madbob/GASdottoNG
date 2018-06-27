<?php

if(!isset($while_shipping))
    $while_shipping = false;

?>

@if($while_shipping == false)
    {{-- In fase di consegna non vengono imposti vincoli, assumendo che chi consegna possa fare quel che vuole (e sappia cosa sta facendo) --}}
    <input type="hidden" name="product-price" value="{{ $product->contextualPrice($order, !$while_shipping) + $product->transport }}" class="skip-on-submit" />
    <input type="hidden" name="product-minimum" value="{{ $product->min_quantity }}" class="skip-on-submit" />
    <input type="hidden" name="product-maximum" value="{{ $product->max_quantity }}" class="skip-on-submit" />
    <input type="hidden" name="product-multiple" value="{{ $product->multiple }}" class="skip-on-submit" />
    <input type="hidden" name="product-partitioning" value="{{ $product->portion_quantity }}" class="skip-on-submit" />

    @if($product->max_available != 0)
        <input type="hidden" name="product-available" value="{{ $product->stillAvailable($order) }}" class="skip-on-submit" />
    @endif
@else
    <input type="hidden" name="product-price" value="{{ $product->contextualPrice($order, !$while_shipping) }}" class="skip-on-submit" />
    <input type="hidden" name="product-transport" value="{{ $product->transport }}" class="skip-on-submit" />
@endif

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
        <input type="text" class="form-control number" name="{{ $product->id }}" value="{{ $populate ? $o->getBookedQuantity($product) : '' }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
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
