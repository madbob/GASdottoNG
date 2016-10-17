@if(isset($editable) == false || $editable == true)
	@include('commons.selectenumfield', [
		'obj' => $order,
		'name' => 'status',
		'label' => 'Stato',
		'values' => [
			[
				'label' => 'Aperto',
				'value' => 'open',
			],
			[
				'label' => 'Sospeso',
				'value' => 'suspended',
			],
			[
				'label' => 'Non Prenotabile',
				'value' => 'closed',
			],
			[
				'label' => 'Consegnato',
				'value' => 'shipped',
			],
			[
				'label' => 'Archiviato',
				'value' => 'archived',
			]
		]
	])
@else
	@include('commons.staticenumfield', [
		'obj' => $order,
		'name' => 'status',
		'label' => 'Stato',
		'values' => [
			[
				'label' => 'Aperto',
				'value' => 'open',
			],
			[
				'label' => 'Sospeso',
				'value' => 'suspended',
			],
			[
				'label' => 'Non Prenotabile',
				'value' => 'closed',
			],
			[
				'label' => 'Consegnato',
				'value' => 'shipped',
			],
			[
				'label' => 'Archiviato',
				'value' => 'archived',
			]
		]
	])
@endif
