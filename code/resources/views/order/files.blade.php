<div class="list-group text-center">
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" tlabel="orders.files.order.shipping" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'shipping'])" />
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" tlabel="orders.files.order.summary" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'summary'])" />
    <x-larastrap::button override_classes="list-group-item list-group-item-action async-modal" postlabel="<i class='bi-window'></i>" tlabel="orders.files.order.table" :data-modal-url="route('orders.export', ['id' => $order->id, 'type' => 'table'])" />
</div>
