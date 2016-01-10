@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userCan('supplier.add'))

		@include('commons.addingbutton', [
			'template' => 'supplier.base-edit',
			'typename' => 'supplier',
			'typename_readable' => 'Fornitore',
			'targeturl' => 'suppliers'
		])

		@endif

		@if($currentgas->userHas('supplier.modify'))

		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleCategories">Amministra Categorie</button>

		<div class="modal fade dynamic-contents" id="handleCategories" tabindex="-1" role="dialog" aria-labelledby="handleCategories" data-contents-url="{{ url('categories') }}">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				</div>
			</div>
		</div>

		@endif
	</div>

	<hr/>
</div>

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'supplier-list', 'items' => $suppliers, 'url' => url('suppliers/')])
	</div>
</div>

<div class="modal fade" id="createCategory" tabindex="-1" role="dialog" aria-labelledby="createCategory">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form class="form-horizontal creating-form" method="POST" action="{{ url('categories') }}" data-toggle="validator">
				<input type="hidden" name="update-select" value="category_id">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Crea Nuova Categoria</h4>
				</div>
				<div class="modal-body">
					@include('commons.selectobjfield', ['obj' => null, 'name' => 'parent_id', 'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(), 'none_selection' => 'Nessuna', 'label' => 'Categoria Padre'])
					@include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
					<button type="submit" class="btn btn-success">Salva</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="createMeasure" tabindex="-1" role="dialog" aria-labelledby="createMeasure">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form class="form-horizontal creating-form" method="POST" action="{{ url('measures') }}" data-toggle="validator">
				<input type="hidden" name="update-select" value="measure_id">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Crea Nuova Unità di Misura</h4>
				</div>
				<div class="modal-body">
					@include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
					<button type="submit" class="btn btn-success">Salva</button>
				</div>
			</form>
		</div>
	</div>
</div>

@endsection
