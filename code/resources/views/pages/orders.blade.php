@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userHas('supplier.orders'))

		@include('commons.addingbutton', [
			'template' => 'order.base-edit',
			'typename' => 'order',
			'typename_readable' => 'Ordine',
			'targeturl' => 'orders'
		])

		@endif
	</div>

	<hr/>
</div>

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'order-list', 'items' => $orders, 'url' => url('orders/')])
	</div>
</div>

@endsection
