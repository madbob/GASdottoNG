<?php

$shippable_status = false;

foreach ($aggregate->orders as $order) {
    if ($currentuser->can('supplier.shippings', $order->supplier)) {
        $shippable_status = true;
        break;
    }
}

$shippable_status = ($shippable_status && $aggregate->isActive() && $aggregate->isRunning() == false);
$more_orders = ($aggregate->orders->count() > 1);
$panel_rand_wrap = rand();

?>

@if($aggregate->isRunning() == false && ($more_orders || $shippable_status))
    <div class="row gray-row">
        <div class="col-md-4">
            @if($shippable_status)
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Notifiche Mail') }}</label>
                        <div class="col-sm-{{ $fieldsize }}">
                            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#notify-aggregate-{{ $aggregate->id }}">{{ _i('Invia Mail') }}</button>
                            <span class="help-block">{{ _i('Ultime notifiche inviate') }}: <span class="last-date" data-updatable-name="last-notification-date-{{ $aggregate->id }}">{{ $aggregate->printableDate('last_notify') }}</span></span>
                        </div>
                    </div>
                </form>

                <div class="modal fade" tabindex="-1" role="dialog" id="notify-aggregate-{{ $aggregate->id }}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form class="form-horizontal inner-form" method="POST" action="{{ url('aggregates/notify/' . $aggregate->id) }}">
                                <input type="hidden" name="update-field" value="last-notification-date-{{ $aggregate->id }}">
                                <input type="hidden" name="close-modal" value="1">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">{{ _i('Notifiche Mail') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">{{ _i('Messaggio (Opzionale)') }}</label>

                                        <div class="col-sm-{{ $fieldsize }}">
                                            <textarea class="form-control" name="message" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                    <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            @if($more_orders)
                <a href="{{ url('aggregates/document/' . $aggregate->id . '/shipping') }}" class="btn btn-default">{{ _i('Dettaglio Consegne Complessivo') }}</a>
            @endif
        </div>
    </div>
    <br/>
@endif

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            @foreach($aggregate->orders as $index => $order)
                <li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#order-{{ $panel_rand_wrap }}-{{ $index }}" role="tab" data-toggle="tab">{{ $order->printableName() }}</a></li>
            @endforeach

            <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ url('/booking/' . $aggregate->id . '/user') }}">{{ _i('Consegne') }}</a></li>

            @if($currentgas->getConfig('fast_shipping_enabled'))
                <li role="presentation"><a href="#fast-shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ url('/deliveries/' . $aggregate->id . '/fast') }}">{{ _i('Consegne Veloci') }}</a></li>
            @endif
        </ul>

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

            <div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}" data-aggregate-id="{{ $aggregate->id }}">
            </div>

            @if($currentgas->getConfig('fast_shipping_enabled'))
                <div role="tabpanel" class="tab-pane fast-shippable-bookings" id="fast-shippings-{{ $aggregate->id }}">
                </div>
            @endif
        </div>
    </div>
</div>

@stack('postponed')
