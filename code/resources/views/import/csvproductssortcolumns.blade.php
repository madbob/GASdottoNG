<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=products&step=run') }}" data-toggle="validator">
        <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />
        <input type="hidden" class="wizard_field" name="supplier_id" value="{{ $supplier->id }}" />

        <div class="modal-body">
            <p>
                {{ _i('Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.') }}
            </p>
            <p>
                {{ _i('Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.') }}
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
                            <li class="list-group-item im_droppable">{{ _i('Colonna') }} <span class="columns_index">{{ $index + 1 }}</span>: <span class="column_content"><input type="hidden" name="column[]" value="none" />{{ _i('[Ignora]') }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group">
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="none" />{{ _i('[Ignora]') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="name" />{{ _i('Nome Prodotto') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="description" />{{ _i('Descrizione') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="price" />{{ _i('Prezzo Unitario') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="transport" />{{ _i('Prezzo Trasporto') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="category" />{{ _i('Categoria') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="measure" />{{ _i('Unità di Misura') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="supplier_code" />{{ _i('Codice Fornitore') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="package_size" />{{ _i('Dimensione Confezione') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="min_quantity" />{{ _i('Ordine Minimo') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="multiple" />{{ _i('Ordinabile per Multipli') }}</li>
                    </ul>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
            <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
        </div>
    </form>
</div>
