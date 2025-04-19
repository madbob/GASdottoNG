<div class="list-group text-center">
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" :label="_i('Dettaglio Consegne')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'shipping'])" />
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" :label="_i('Riassunto Prodotti')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'summary'])" />
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" :label="_i('Tabella Complessiva Prodotti')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'table'])" />
</div>
