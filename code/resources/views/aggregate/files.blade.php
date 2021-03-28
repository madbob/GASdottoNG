<div class="list-group">
    <a href="{{ route('aggregates.export', ['id' => $aggregate->id, 'type' => 'shipping']) }}" class="list-group-item async-modal">
        {{ _i('Dettaglio Consegne Aggregato') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
    <a href="{{ route('aggregates.export', ['id' => $aggregate->id, 'type' => 'summary']) }}" class="list-group-item async-modal">
        {{ _i('Riassunto Prodotti Aggregato') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
</div>
