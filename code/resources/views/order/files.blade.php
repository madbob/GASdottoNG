<div class="list-group">
    <a href="{{ route('orders.export', ['id' => $order->id, 'type' => 'shipping']) }}" class="list-group-item async-modal">
        {{ _i('Dettaglio Consegne') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
    <a href="{{ route('orders.export', ['id' => $order->id, 'type' => 'summary']) }}" class="list-group-item async-modal">
        {{ _i('Riassunto Prodotti') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
    <a href="{{ route('orders.export', ['id' => $order->id, 'type' => 'table']) }}" class="list-group-item async-modal">
        {{ _i('Tabella Complessiva Prodotti') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
</div>
