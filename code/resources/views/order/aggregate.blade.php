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
    @if($aggregate->isActive())
        <hr/>

        <div class="row">
            <div class="col-md-6">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">Notifiche Mail</label>
                        <div class="col-sm-{{ $fieldsize }}">
                            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#notify-aggregate-{{ $aggregate->id }}">Invia Mail</button> Ultime notifiche inviate: <span class="last-date" data-updatable-name="last-notification-date-{{ $aggregate->id }}">{{ $aggregate->printableDate('last_notify') }}</span>
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
                                    <h4 class="modal-title">Notifiche Mail</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Messaggio (Opzionale)</label>

                                        <div class="col-sm-{{ $fieldsize }}">
                                            <textarea class="form-control" name="message" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">Salva</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <hr/>

    <div class="row aggregate-bookings">
        <input type="hidden" name="aggregate_id" value="{{ $aggregate->id }}" />

        <div class="col-md-12">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne</a></li>

                @if($currentgas->getConfig('fast_shipping_enabled'))
                    <li role="presentation"><a href="#fast-shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne Veloci</a></li>
                @endif
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}">
                </div>
                @if($currentgas->getConfig('fast_shipping_enabled'))
                    <div role="tabpanel" class="tab-pane fast-shippable-bookings" id="fast-shippings-{{ $aggregate->id }}">
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

@stack('postponed')
