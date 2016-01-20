@include('commons.selectobjfield', ['obj' => $order, 'name' => 'supplier_id', 'objects' => App\Supplier::orderBy('name', 'asc')->get(), 'label' => 'Fornitore', 'mandatory' => true])
@include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
@include('commons.datefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
@include('commons.datefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])

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
			'value' => 'private',
		],
		[
			'label' => 'In Consegna',
			'value' => 'shipping',
		],
		[
			'label' => 'Consegnato',
			'value' => 'shipped',
		],
		[
			'label' => 'Chiuso',
			'value' => 'closed',
		]
	]
])
