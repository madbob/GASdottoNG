<div class="list-group">
    <x-larastrap::ambutton classes="list-group-item" :label="_i('Dettaglio Consegne')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'shipping'])" />
    <x-larastrap::ambutton classes="list-group-item" :label="_i('Riassunto Prodotti')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'summary'])" />
    <x-larastrap::ambutton classes="list-group-item" :label="_i('Tabella Complessiva Prodotti')" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'table'])" />
</div>
