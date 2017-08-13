<?php

$has_shipping = false;

foreach ($aggregate->orders as $order) {
    if ($currentuser->can('supplier.shippings', $order->supplier)) {
        $has_shipping = true;
        break;
    }
}

$more_orders = ($aggregate->orders->count() > 1);
$panel_rand_wrap = rand();

?>

<div class="row">
    <div class="col-md-12">
        @if($more_orders)
            <ul class="nav nav-tabs" role="tablist">
                @foreach($aggregate->orders as $index => $order)
                    <li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#order-{{ $panel_rand_wrap }}-{{ $index }}" role="tab" data-toggle="tab">{{ $order->printableName() }}</a></li>
                @endforeach
            </ul>
        @endif

        <div class="tab-content">
            @foreach($aggregate->orders as $index => $order)
                <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="order-{{ $panel_rand_wrap }}-{{ $index }}">
                    @can('supplier.orders', $order->supplier)
                        @include('order.edit', ['order' => $order])
                    @else
                        @include('order.show', ['order' => $order])
                    @endcan
                </div>
            @endforeach
        </div>
    </div>
</div>

@if($has_shipping && $aggregate->isActive() && $aggregate->isRunning() == false)
    <hr/>

    <div class="row aggregate-bookings">
        <input type="hidden" name="aggregate_id" value="{{ $aggregate->id }}" />

        <div class="col-md-12">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne</a></li>
                <li role="presentation"><a href="#fast-shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne Veloci</a></li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}">
                </div>
                <div role="tabpanel" class="tab-pane fast-shippable-bookings" id="fast-shippings-{{ $aggregate->id }}">
                </div>
            </div>
        </div>
    </div>
@endif

@stack('postponed')
