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
$master_summary = $aggregate->reduxData();

?>

<div class="row">
    @if($controllable && ($shippable_status || $shipped_status))
        <div class="col-12">
            <div class="row gray-row order-extras mb-3">
                <div class="col-6">
                    <?php $send_mail_label = $shippable_status ? _i('Invia Riepiloghi Prenotazioni') : _i('Invia Riepiloghi Consegne') ?>

                    <x-larastrap::field :label="$send_mail_label" :pophelp="_i('Invia a tutti gli utenti che hanno partecipato all\'ordine una mail riassuntiva della propria prenotazione. È possibile aggiungere un messaggio da allegare a tutti, per eventuali segnalazioni addizionali. Il messaggio di riepilogo viene automaticamente inviato alla chiusura dell\'ordine, automatica o manuale che sia, se configurato dal pannello Configurazioni.')">
                        <x-larastrap::mbutton :label="_i('Invia Mail')" :triggers_modal="sprintf('notify-aggregate-%s', $aggregate->id)" />
                        <small>{{ _i('Ultime notifiche inviate') }}: <span class="last-date" data-updatable-name="last-notification-date-{{ $aggregate->id }}">{{ $aggregate->printableDate('last_notify') }}</span></small>
                    </x-larastrap::field>

                    <x-larastrap::modal :title="_i('Notifiche Mail')" :id="sprintf('notify-aggregate-%s', $aggregate->id)">
                        <x-larastrap::form method="POST" :action="url('aggregates/notify/' . $aggregate->id)">
                            <input type="hidden" name="update-field" value="last-notification-date-{{ $aggregate->id }}">
                            <input type="hidden" name="close-modal" value="1">
                            <x-larastrap::textarea name="message" :label="_i('Messaggio (Opzionale)')" rows="5" />
                        </x-larastrap::form>
                    </x-larastrap::modal>
                </div>
            </div>
        </div>
    @endif

    <div class="col-12">
        <x-larastrap::tabs>
            @foreach($aggregate->orders as $index => $order)
                <x-larastrap::tabpane :label="$order->printableName()" :active="$index == 0">
                    @can('supplier.orders', $order->supplier)
                        @include('order.edit', ['order' => $order, 'master_summary' => $master_summary])
                    @else
                        @include('order.show', ['order' => $order, 'master_summary' => $master_summary])
                    @endcan
                </x-larastrap::tabpane>
            @endforeach

            @if($controllable && $more_orders)
                <x-larastrap::remotetabpane :label="_i('Aggregato')" :button_attributes="['data-tab-url' => route('aggregates.details', $aggregate->id)]">
                </x-larastrap::remotetabpane>
            @endif

            @if($multi_gas)
                <x-larastrap::remotetabpane :label="_i('Multi-GAS')" :button_attributes="['data-tab-url' => route('aggregates.multigas', $aggregate->id)]">
                </x-larastrap::remotetabpane>
            @endif

            @can('supplier.shippings', $aggregate)
                <x-larastrap::remotetabpane :label="_i('Consegne')" :button_attributes="['data-tab-url' => url('/booking/' . $aggregate->id . '/user')]">
                </x-larastrap::remotetabpane>

                @if($fast_shipping_enabled)
                    <x-larastrap::remotetabpane :label="_i('Consegne Veloci')" :button_attributes="['data-tab-url' => url('/deliveries/' . $aggregate->id . '/fast')]">
                    </x-larastrap::remotetabpane>
                @endif
            @endcan
        </x-larastrap::tabs>
    </div>

    @stack('postponed')
</div>
