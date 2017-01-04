<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=users&step=run') }}" data-toggle="validator">
        <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

        <div class="modal-body">
            <p>
                Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.
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
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="firstname" />Nome</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="lastname" />Cognome</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="username" />Login</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="email" />E-Mail</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="phone" />Telefono</li>
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
