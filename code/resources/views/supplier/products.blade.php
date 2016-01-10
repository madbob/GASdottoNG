@if($supplier->userCan('supplier.modify'))

@include('commons.addingbutton', [
	'template' => 'product.base-edit',
	'typename' => 'product',
	'target_update' => 'product-list-' . $supplier->id,
	'typename_readable' => 'Prodotto',
	'targeturl' => 'products',
	'extra' => ['supplier_id' => $supplier->id]
])

<div class="clearfix"></div>
<hr />

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'product-list-' . $supplier->id, 'items' => $supplier->products, 'url' => url('products/')])
	</div>
</div>
