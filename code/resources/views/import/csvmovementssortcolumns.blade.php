<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=movements&step=select') }}" data-toggle="validator">
        <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

        <div class="modal-body">
            <p>
                {{ _i('Clicca e trascina gli attributi sulla destra nel blocco centrale, per assegnare ad ogni colonna del tuo file un significato.') }}
            </p>
            <p>
                {{ _i('Gli utenti sono identificati per username o indirizzo mail (che deve essere univoco!).') }}
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
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="date" />{{ _i('Data') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="amount" />{{ _i('Valore') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="notes" />{{ _i('Note') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="user" />{{ _i('Utente') }}</li>
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
