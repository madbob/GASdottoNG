<div class="list-group">
    <x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.order.shipping" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'shipping'])" />
    <x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.order.summary" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'summary'])" />
    <x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.order.table" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'table'])" />
</div>
