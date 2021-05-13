<?php

$shippable_status = false;
$controllable = false;
$fast_shipping_enabled = false;

foreach ($aggregate->orders as $order) {
    if ($currentuser->can('supplier.shippings', $order->supplier)) {
        $controllable = true || $controllable;
    }

    if ($order->supplier->fast_shipping_enabled) {
        $fast_shipping_enabled = true || $fast_shipping_enabled;
    }
}

$shippable_status = ($controllable && $aggregate->isActive() && $aggregate->isRunning() == false);
$shipped_status = ($controllable && $aggregate->isActive() == false && $aggregate->isRunning() == false);
$more_orders = ($aggregate->orders->count() > 1);
$multi_gas = ($aggregate->gas()->count() > 1 && $currentuser->can('gas.multi', $currentuser->gas));
$panel_rand_wrap = rand();

?>

@if($controllable && ($shippable_status || $shipped_status))
    <div class="row gray-row order-extras">
        <div class="col-md-6">
            <form class="form-horizontal">
                <label class="col-sm-{{ $labelsize }} control-label">
                    @include('commons.helpbutton', ['help_popover' => _i("Invia a tutti gli utenti che hanno partecipato all'ordine una mail riassuntiva della propria prenotazione. È possibile aggiungere un messaggio da allegare a tutti, per eventuali segnalazioni addizionali. Il messaggio di riepilogo viene automaticamente inviato alla chiusura dell'ordine, automatica o manuale che sia, se configurato dal pannello Configurazioni.")])
                    @if($shippable_status)
                        {{ _i('Invia Riepiloghi Prenotazioni') }}
                    @else
                        {{ _i('Invia Riepiloghi Consegne') }}
                    @endif
                </label>
                <div class="col-sm-{{ $fieldsize }}">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#notify-aggregate-{{ $aggregate->id }}">{{ _i('Invia Mail') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
                    <span class="help-block">{{ _i('Ultime notifiche inviate') }}: <span class="last-date" data-updatable-name="last-notification-date-{{ $aggregate->id }}">{{ $aggregate->printableDate('last_notify') }}</span></span>
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
        </div>
        <div class="col-md-6">
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

            @if($controllable && $more_orders)
                <li role="presentation"><a href="#aggregate-metadata-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ route('aggregates.details', $aggregate->id) }}">{{ _i('Aggregato') }}</a></li>
            @endif

            @if($multi_gas)
                <li role="presentation"><a href="#aggregate-multigas-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ route('aggregates.multigas', $aggregate->id) }}">{{ _i('Multi-GAS') }}</a></li>
            @endif

            @can('supplier.shippings', $aggregate)
                <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ url('/booking/' . $aggregate->id . '/user') }}">{{ _i('Consegne') }}</a></li>

                @if($fast_shipping_enabled)
                    <li role="presentation"><a href="#fast-shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ url('/deliveries/' . $aggregate->id . '/fast') }}">{{ _i('Consegne Veloci') }}</a></li>
                @endif
            @endcan
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

            @if($controllable && $more_orders)
                <div role="tabpanel" class="tab-pane" id="aggregate-metadata-{{ $aggregate->id }}">
                </div>
            @endif

            @if($multi_gas)
                <div role="tabpanel" class="tab-pane" id="aggregate-multigas-{{ $aggregate->id }}" data-aggregate-id="{{ $aggregate->id }}">
                </div>
            @endif

            <div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}" data-aggregate-id="{{ $aggregate->id }}">
            </div>

            @if($fast_shipping_enabled)
                <div role="tabpanel" class="tab-pane fast-shippable-bookings" id="fast-shippings-{{ $aggregate->id }}">
                </div>
            @endif
        </div>
    </div>
</div>

@stack('postponed')
