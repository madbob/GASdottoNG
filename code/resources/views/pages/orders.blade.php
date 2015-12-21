@extends($theme_layout)

@section('content')

@if($currentgas->userHas('supplier.orders'))

@include('commons.addingbutton', [
	'template' => 'order.base-edit',
	'typename' => 'order',
	'typename_readable' => 'Ordine',
	'targeturl' => 'orders'
])

<hr/>

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'order-list', 'items' => $orders, 'url' => url('orders/')])
	</div>
</div>

@endsection
