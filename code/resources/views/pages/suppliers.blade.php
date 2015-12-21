@extends($theme_layout)

@section('content')

@if($currentgas->userCan('supplier.add'))

@include('commons.addingbutton', [
	'template' => 'supplier.base-edit',
	'typename' => 'supplier',
	'typename_readable' => 'Fornitore',
	'targeturl' => 'suppliers'
])

<hr/>

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'supplier-list', 'items' => $suppliers, 'url' => url('suppliers/')])
	</div>
</div>

@endsection
