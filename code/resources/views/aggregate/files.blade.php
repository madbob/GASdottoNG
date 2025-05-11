<div class="list-group">
    <x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.aggregate.shipping" :data-modal-url="route('aggregates.export', ['id' => $aggregate->id, 'type' => 'shipping', 'managed_gas' => $managed_gas])" />
    <x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.aggregate.summary" :data-modal-url="route('aggregates.export', ['id' => $aggregate->id, 'type' => 'summary', 'managed_gas' => $managed_gas])" />
	<x-larastrap::ambutton classes="list-group-item" tlabel="orders.files.aggregate.table" :data-modal-url="route('aggregates.export', ['id' => $aggregate->id, 'type' => 'table', 'managed_gas' => $managed_gas])" />
</div>
