@if($supplier->userCan('supplier.modify'))

<div class="row">
	<div class="col-md-12">

		@include('commons.addingbutton', [
			'template' => 'product.base-edit',
			'typename' => 'product',
			'target_update' => 'product-list-' . $supplier->id,
			'typename_readable' => 'Prodotto',
			'targeturl' => 'products',
			'extra' => ['supplier_id' => $supplier->id]
		])

		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#importCSV{{ $supplier->id }}">Importa CSV</button>

		<div class="modal fade wizard" id="importCSV{{ $supplier->id }}" tabindex="-1" role="dialog" aria-labelledby="importCSV{{ $supplier->id }}">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Importa CSV</h4>
					</div>
					<div class="wizard_page">
						<form class="form-horizontal" method="POST" action="{{ url('import/csv?step=guess') }}" data-toggle="validator" enctype="multipart/form-data">
							<input type="hidden" name="supplier_id" value="{{ $supplier->id }}" />
							<div class="modal-body">
								<p>
									Sono ammessi solo files in formato CSV.
								</p>

								<hr/>

								@include('commons.filefield', [
									'obj' => null,
									'name' => 'file',
									'label' => 'File da Caricare',
									'mandatory' => true,
									'extras' => [
										'data-url' => 'import/csv?step=guess',
										'data-form-data' => '{"supplier_id": "' . $supplier->id . '"}',
										'data-run-callback' => 'wizardLoadPage'
									]
								])
							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
								<button type="submit" class="btn btn-success">Avanti</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="clearfix"></div>
<hr />

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'product-list-' . $supplier->id, 'items' => $supplier->products, 'url' => url('products/')])
	</div>
</div>
