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

		@if($currentgas->userCan('categories.admin'))

		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#handleCategories">Amministra Categorie</button>

		<div class="modal fade dynamic-contents" id="handleCategories" tabindex="-1" role="dialog" aria-labelledby="handleCategories" data-contents-url="{{ url('categories') }}">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				</div>
			</div>
		</div>

		@endif
	</div>

	<div class="clearfix"></div>
	<hr/>
</div>

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'supplier-list', 'items' => $suppliers, 'url' => url('suppliers/')])
	</div>
</div>

@if($currentgas->userCan('categories.admin'))
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
					@include('commons.selectobjfield', [
						'obj' => null,
						'name' => 'parent_id',
						'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
						'extra_selection' => ['null' => 'Nessuna'],
						'label' => 'Categoria Padre'
					])

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
@endif

@if($currentgas->userCan('measures.admin'))
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
@endif

@if($currentgas->userHas('supplier.modify'))
<div class="modal fade" id="editPermissions" tabindex="-1" role="dialog" aria-labelledby="editPermissions">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Modifica Permessi</h4>
			</div>
			<div class="modal-body">
				<div class="row permissions-editor">
					<input type="hidden" name="subject" value="">
					<input type="hidden" name="rule" value="">

					<div class="col-md-6">
						<select multiple name="user" class="form-control" size="20">
							<option disabled="disabled">Seleziona una regola</option>
						</select>
					</div>

					<div class="col-md-6">
						<div class="form-group">
							<button class="btn btn-danger remove-auth">Rimuovi Utente Selezionato</button>
						</div>
						<div class="form-group">
							<input name="adduser" class="form-control" placeholder="Digita il nome di un utente da aggiungere all'elenco" />
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="behaviour" value="selected">
								Autorizza solo gli utenti nell'elenco
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="behaviour" value="except">
								Autorizza tutti, tranne gli utenti nell'elenco
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="behaviour" value="all">
								Autorizza tutti gli utenti (indipendentemente dall'elenco)
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default reloader" data-dismiss="modal" data-reload-target="#supplier-list">Chiudi</button>
			</div>
		</div>
	</div>
</div>
@endif

@endsection
