<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=users&step=run') }}" data-toggle="validator">
        <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

        <div class="modal-body">
            <p>
                {{ _i('Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.') }}
            </p>

            <hr/>

            <div id="import_csv_sorter">
                <div class="col-md-4">
                    <ul class="list-group">
                        @foreach($columns as $column)
                            <li class="list-group-item">{{ empty($column) ? '&nbsp;' : $column }}</li>
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
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="firstname" />{{ _i('Nome') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="lastname" />{{ _i('Cognome') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="username" />{{ _i('Login') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="email" />{{ _i('E-Mail') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="phone" />{{ _i('Telefono') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="member_since" />{{ _i('Membro Dal') }}</li>
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="credit" />{{ _i('Credito Attuale') }}</li>
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
