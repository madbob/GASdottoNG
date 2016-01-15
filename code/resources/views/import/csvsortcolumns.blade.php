<div class="wizard_page">
	<form class="form-horizontal" method="POST" action="{{ url('import/csv?step=run') }}" data-toggle="validator">
		<input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />
		<input type="hidden" class="wizard_field" name="supplier_id" value="{{ $supplier->id }}" />

		<div class="modal-body">
			<p>
				Clicca e trascina gli attributi sulla destra nel blocco centrale, per assegnare ad ogni colonna del tuo file un significato.
			</p>
			<p>
				Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.
			</p>

			<hr/>

			<div id="import_csv_sorter">
				<div class="col-md-4">
					<ul class="list-group">
						@foreach($columns as $column)
						<li class="list-group-item">{{ $column }}</li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-4">
					<ul class="list-group">
						@foreach($columns as $index => $column)
						<li class="list-group-item im_droppable">Colonna <span class="columns_index">{{ $index + 1 }}</span>: <span class="column_content"><input type="hidden" name="column[]" value="none" />[Ignora]</span></li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-4">
					<ul class="list-group">
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="none" />[Ignora]</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="name" />Nome Prodotto</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="description" />Descrizione</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="price" />Prezzo Unitario</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="transport" />Prezzo Trasporto</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="category" />Categoria</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="measure" />Unità di Misura</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="package" />Dimensione Confezione</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="minimum" />Ordine Minimo</li>
						<li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="multiple" />Ordinabile per Multipli</li>
					</ul>
				</div>
			</div>

			<div class="clearfix"></div>
		</div>

		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
			<button type="submit" class="btn btn-success">Avanti</button>
		</div>
	</form>
</div>
