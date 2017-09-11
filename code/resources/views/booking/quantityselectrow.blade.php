<?php

if(!isset($while_shipping))
    $while_shipping = false;

?>

<input type="hidden" name="product-price" value="{{ $product->contextualPrice($order, !$while_shipping) + $product->transport }}" class="skip-on-submit" />

@if($while_shipping == false)
    {{-- In fase di consegna non vengono imposti vincoli, assumendo che chi consegna possa fare quel che vuole (e sappia cosa sta facendo) --}}
    <input type="hidden" name="product-minimum" value="{{ $product->min_quantity }}" class="skip-on-submit" />
    <input type="hidden" name="product-maximum" value="{{ $product->max_quantity }}" class="skip-on-submit" />
    <input type="hidden" name="product-multiple" value="{{ $product->multiple }}" class="skip-on-submit" />
    <input type="hidden" name="product-available" value="{{ $product->stillAvailable($order) }}" class="skip-on-submit" />
    <input type="hidden" name="product-partitioning" value="{{ $product->portion_quantity }}" class="skip-on-submit" />
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
        <input step="any" min="0" type="number" class="form-control" name="{{ $product->id }}" value="{{ $populate ? $o->getBookedQuantity($product) : '' }}" {{ $order->isActive() == false ? 'disabled' : '' }} />
        <div class="input-group-addon">
            @if($while_shipping)
                {{ $product->measure->name }}
            @else
                {{ $product->printableMeasure() }}
            @endif
        </div>
    </div>
@endif
