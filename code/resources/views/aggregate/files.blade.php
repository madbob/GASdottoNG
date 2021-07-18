<div class="list-group">
    <x-larastrap::ambutton classes="list-group-item" :label="_i('Dettaglio Consegne Aggregato')" :data-modal-url="route('aggregates.export', ['id' => $aggregate->id, 'type' => 'shipping', 'managed_gas' => $managed_gas])" />
    <x-larastrap::ambutton classes="list-group-item" :label="_i('Riassunto Prodotti Aggregato')" :data-modal-url="route('aggregates.export', ['id' => $aggregate->id, 'type' => 'summary', 'managed_gas' => $managed_gas])" />
</div>
