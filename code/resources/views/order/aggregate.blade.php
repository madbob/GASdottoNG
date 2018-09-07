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
    <div class="row gray-row order-extras">
        <div class="col-md-6">
            @if($shippable_status)
                <form class="form-horizontal">
                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Notifiche Mail') }}</label>
                    <div class="col-sm-{{ $fieldsize }}">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#notify-aggregate-{{ $aggregate->id }}">{{ _i('Invia Mail') }}</button>
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
            @endif
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

            @if($more_orders)
                <li role="presentation"><a href="#aggregate-metadata-{{ $aggregate->id }}" role="tab" data-toggle="tab">{{ _i('Aggregato') }}</a></li>
            @endif

            @can('supplier.shippings', $aggregate)
                <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab" data-async-load="{{ url('/booking/' . $aggregate->id . '/user') }}">{{ _i('Consegne') }}</a></li>

                @if($currentgas->getConfig('fast_shipping_enabled'))
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

            @if($more_orders)
                <div role="tabpanel" class="tab-pane" id="aggregate-metadata-{{ $aggregate->id }}">
                    <form class="form-horizontal main-form" method="PUT" action="{{ route('aggregates.update', $aggregate->id) }}">
                        <div class="row">
                            <div class="col-md-4">
                                @include('commons.textfield', ['obj' => $aggregate, 'name' => 'comment', 'label' => _i('Commento')])
                            </div>
                            <div class="col-md-4">
                            </div>
                            <div class="col-md-4">
                                <div class="list-group pull-right">
                                    <a href="#" class="list-group-item" data-toggle="modal" data-target="#shipping-products-aggregate-document-{{ $aggregate->id }}">{{ _i('Dettaglio Consegne Aggregato') }}</a>
                                </div>
                            </div>
                        </div>

                        @include('commons.formbuttons', [
                            'no_delete' => true
                        ])
                    </form>

                    <div class="modal fade close-on-submit" id="shipping-products-aggregate-document-{{ $aggregate->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-extra-lg" role="document">
                            <div class="modal-content">
                                <form class="form-horizontal direct-submit" method="GET" action="{{ url('aggregates/document/' . $aggregate->id . '/shipping') }}" data-toggle="validator" novalidate>
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">{{ _i('Dettaglio Consegne Aggregato') }}</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            {{ _i("Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni di tutti gli ordini inclusi in questo aggregato.") }}
                                        </p>

                                        @if($currentgas->deliveries->isEmpty() == false)
                                            @include('commons.radios', [
                                                'name' => 'shipping_place',
                                                'label' => _i('Luogo di Consegna'),
                                                'labelsize' => 2,
                                                'fieldsize' => 10,
                                                'values' => array_merge(
                                                    [0 => (object)['name' => 'Tutti', 'checked' => true]],
                                                    as_choosable($currentgas->deliveries, function($i, $a) {
                                                        return $a->id;
                                                    }, function($i, $a) {
                                                        return $a->name;
                                                    }, function($i, $a) {
                                                        return false;
                                                    })
                                                )
                                            ])
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                        <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
