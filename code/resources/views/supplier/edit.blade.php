<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#details-{{ $supplier->id }}" aria-controls="dettagli" role="tab" data-toggle="tab">Dettagli</a></li>
	<li role="presentation"><a href="#orders-{{ $supplier->id }}" aria-controls="ordini" role="tab" data-toggle="tab">Ordini</a></li>
	<li role="presentation"><a href="#products-{{ $supplier->id }}" aria-controls="prodotti" role="tab" data-toggle="tab">Prodotti</a></li>
	<li role="presentation"><a href="#files-{{ $supplier->id }}" aria-controls="files" role="tab" data-toggle="tab">Files</a></li>
</ul>

<div class="tab-content">
	<div role="tabpanel" class="tab-pane active" id="details-{{ $supplier->id }}">
		<form class="form-horizontal main-form supplier-editor" method="PUT" action="{{ url('suppliers/' . $supplier->id) }}">
			<input type="hidden" name="id" value="{{ $supplier->id }}" />

			<div class="row">
				<div class="col-md-6">
					@include('supplier.base-edit', ['supplier' => $supplier])
				</div>
				<div class="col-md-6">
					<div class="alert alert-info">
						<p>TODO</p>
					</div>
				</div>
			</div>

			@include('commons.formbuttons')
		</form>
	</div>

	<div role="tabpanel" class="tab-pane fade" id="orders-{{ $supplier->id }}">
		@include('supplier.orders', ['supplier' => $supplier])
	</div>

	<div role="tabpanel" class="tab-pane fade" id="products-{{ $supplier->id }}">
		@include('supplier.products', ['supplier' => $supplier])
	</div>

	<div role="tabpanel" class="tab-pane fade" id="files-{{ $supplier->id }}">
		<div class="alert alert-info">
			<p>TODO</p>
		</div>
	</div>
</div>
