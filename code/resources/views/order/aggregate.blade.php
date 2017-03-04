<?php

$has_shipping = false;

foreach ($aggregate->orders as $order) {
    if ($order->supplier->userCan('supplier.shippings')) {
        $has_shipping = true;
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
                    @if($order->supplier->userCan('supplier.orders'))
                        <?php $summary = $order->calculateSummary() ?>

                        <form class="form-horizontal main-form order-editor" method="PUT" action="{{ url('orders/' . $order->id) }}">
                            <input type="hidden" name="id" value="{{ $order->id }}" />

                            <div class="row">
                                <div class="col-md-4">
                                    @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
                                    @include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])

                                    @include('commons.datefield', [
                                        'obj' => $order,
                                        'name' => 'end',
                                        'label' => 'Data Chiusura',
                                        'mandatory' => true,
                                        'extras' => [
                                            'data-enforce-after' => '.date[name=start]'
                                        ]
                                    ])

                                    @include('commons.datefield', [
                                        'obj' => $order,
                                        'name' => 'shipping',
                                        'label' => 'Data Consegna',
                                        'extras' => [
                                            'data-enforce-after' => '.date[name=end]'
                                        ]
                                    ])

                                    @include('commons.orderstatus', ['order' => $order])
                                </div>
                                <div class="col-md-4">
                                    @include('commons.textfield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale', 'postlabel' => '€ / %'])

                                    @if($currentgas->userCan('movements.view|movements.admin'))
                                        @include('commons.movementfield', ['obj' => $order->payment, 'name' => 'payment_id', 'label' => 'Pagamento', 'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered)])
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="well">
                                        <h4>Files</h4>

                                        <div class="list-group">
                                            @foreach($order->attachments as $attachment)
                                                <a href="{{ $attachment->download_url }}" class="list-group-item">{{ $attachment->name }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr/>

                            @include('order.summary', ['order' => $order, 'summary' => $summary])
                            @include('commons.formbuttons')
                        </form>
                    @else
                        <form class="form-horizontal main-form">
                            <div class="col-md-4">
                                @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
                                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
                                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
                                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
                                @include('commons.orderstatus', ['order' => $order, 'editable' => false])
                            </div>

                            <div class="col-md-4">
                            </div>

                            @if($order->supplier->userCan('supplier.shippings'))
                                <div class="col-md-4">
                                    <div class="well">
                                        <h4>Files</h4>

                                        <div class="list-group">
                                            @foreach($order->attachments as $attachment)
                                                <a href="{{ $attachment->download_url }}" class="list-group-item">{{ $attachment->name }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

@if(($has_shipping && $aggregate->isActive()) || $aggregate->isRunning())
    <hr/>

    <div class="row aggregate-bookings">
        <input type="hidden" name="aggregate_id" value="{{ $aggregate->id }}" />

        <div class="col-md-12">
            <ul class="nav nav-tabs" role="tablist">
                @if($aggregate->isRunning())
                    <li role="presentation"><a href="#myself-{{ $aggregate->id }}" role="tab" data-toggle="tab">La Mia Prenotazione</a></li>
                @endif

                @if($has_shipping)
                    @if($aggregate->isActive())
                        <li role="presentation"><a href="#others-{{ $aggregate->id }}" role="tab" data-toggle="tab">Prenotazioni per Altri</a></li>
                    @endif

                    @if($aggregate->isRunning() == false)
                        <li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne</a></li>
                    @endif
                @endif
            </ul>

            <div class="tab-content">
                @if($aggregate->isRunning())
                    <div role="tabpanel" class="tab-pane" id="myself-{{ $aggregate->id }}">
                        @include('booking.edit', ['aggregate' => $aggregate, 'user' => $currentuser])
                    </div>
                @endif

                @if($has_shipping)
                    @if($aggregate->isActive())
                        <div role="tabpanel" class="tab-pane" id="others-{{ $aggregate->id }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="Cerca Utente" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 other-booking">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($aggregate->isRunning() == false)
                        <div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}">
                            @include('booking.list', ['aggregate' => $aggregate])
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif

@stack('postponed')
