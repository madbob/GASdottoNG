<?php

$orders = $aggregate->orders()->with([
    'products', 'products.measure', 'products.category',
    'payment', 'modifiers', 'circles',
])->get();

$aggregate->setRelation('orders', $orders);

$shippable_status = false;
$controllable = false;
$fast_shipping_enabled = false;

foreach ($orders as $order) {
    $order->setRelation('aggregate', $aggregate);
    $order->angryBookings();

    if ($currentuser->can('supplier.shippings', $order->supplier)) {
        $controllable = true;
    }

    if ($order->supplier->fast_shipping_enabled) {
        $fast_shipping_enabled = true;
    }
}

$shippable_status = ($controllable && $aggregate->isActive() && $aggregate->isRunning() == false);
$shipped_status = ($controllable && $aggregate->isActive() == false && $aggregate->isRunning() == false);
$more_orders = ($orders->count() > 1);
$multi_gas = ($aggregate->gas->count() > 1 && $currentuser->can('gas.multi', $currentuser->gas));
$panel_rand_wrap = rand();
$master_summary = $aggregate->reduxData();

?>

<div class="row">
    @if($controllable && ($shippable_status || $shipped_status))
        <div class="col-12">
            <div class="row gray-row order-extras mb-3">
                <div class="col-6">
                    @php

                    if ($shippable_status) {
                        $send_mail_label = __('texts.orders.send_booking_summaries');
                        $send_mail_hint = __('texts.orders.help.send_booking_summaries');
                    }
                    else {
                        $send_mail_label = __('texts.orders.send_delivery_summaries');
                        $send_mail_hint = __('texts.orders.help.send_delivery_summaries');
                    }

                    @endphp

                    <x-larastrap::field margins="0 0 0 0" :label="$send_mail_label" tpophelp="orders.help.send_summaries">
                        <x-larastrap::mbutton tlabel="generic.send_mail" :triggers_modal="sprintf('notify-aggregate-%s', $aggregate->id)" />
                        <small>{{ __('texts.orders.last_summaries_date') }}: <span class="last-date" data-updatable-name="last-notification-date-{{ $aggregate->id }}">{{ $aggregate->printableDate('last_notify') }}</span></small>
                    </x-larastrap::field>

                    <x-larastrap::modal :id="sprintf('notify-aggregate-%s', $aggregate->id)">
                        <x-larastrap::iform method="POST" :action="url('aggregates/notify/' . $aggregate->id)">
                            <x-larastrap::suggestion>
                                <p>
                                    {{ $send_mail_hint }}
                                </p>
                                <p>
                                    {{ __('texts.orders.summaries_recipients_count', ['count' => count($aggregate->notifiableBookings())]) }}
                                </p>
                            </x-larastrap::suggestion>

                            <input type="hidden" name="update-field" value="last-notification-date-{{ $aggregate->id }}">
                            <input type="hidden" name="close-modal" value="1">
                            <x-larastrap::textarea name="message" tlabel="generic.optional_message" rows="5" />
                        </x-larastrap::iform>
                    </x-larastrap::modal>
                </div>
            </div>
        </div>
    @endif

    <div class="col-12">
        <x-larastrap::tabs :id="md5($orders->pluck('id')->join(''))">
            @foreach($orders as $index => $order)
                <x-larastrap::tabpane :label="$order->printableName()" :active="$index == 0" :icon="$order->statusIcons()">
                    @can('supplier.orders', $order->supplier)
                        @include('order.edit', ['order' => $order, 'master_summary' => $master_summary])
                    @else
                        @include('order.show', ['order' => $order, 'master_summary' => $master_summary])
                    @endcan
                </x-larastrap::tabpane>
            @endforeach

            @if($controllable && $more_orders)
				<x-larastrap::tabpane tlabel="orders.aggregate" icon="bi-plus-circle">
					@include('aggregate.details', ['aggregate' => $aggregate, 'master_summary' => $master_summary])
				</x-larastrap::tabpane>
            @endif

            @if($multi_gas)
                <x-larastrap::remotetabpane tlabel="generic.menu.multigas" :button_attributes="['data-tab-url' => route('aggregates.multigas', $aggregate->id)]" icon="bi-people">
                </x-larastrap::remotetabpane>
            @endif

            @can('supplier.shippings', $aggregate)
                <x-larastrap::remotetabpane tlabel="orders.deliveries" :button_attributes="['data-tab-url' => url('/booking/' . $aggregate->id . '/user')]" icon="bi-truck">
                </x-larastrap::remotetabpane>

                @if($fast_shipping_enabled)
                    <x-larastrap::remotetabpane tlabel="orders.fast_deliveries" :button_attributes="['data-tab-url' => url('/deliveries/' . $aggregate->id . '/fast')]" icon="bi-rocket-takeoff">
                    </x-larastrap::remotetabpane>
                @endif
            @endcan
        </x-larastrap::tabs>
    </div>

    @stack('postponed')
</div>
