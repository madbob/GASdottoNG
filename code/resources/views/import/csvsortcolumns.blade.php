<?php

if (!isset($next_step)) {
    $next_step = 'run';
}

if (!isset($extra_fields)) {
    $extra_fields = [];
}

if (!isset($extra_description)) {
    $extra_description = [];
}

?>

<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=' . $type . '&step=' . $next_step) }}" data-toggle="validator">
        <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

        @foreach($extra_fields as $name => $value)
            <input type="hidden" class="wizard_field" name="{{ $name }}" value="{{ $value }}" />
        @endforeach

        <div class="modal-body">
            <p>
                {{ _i('Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.') }}
            </p>

            @foreach($extra_description as $ed)
                <p>{{ $ed }}</p>
            @endforeach

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
                        @foreach($sorting_fields as $name => $label)
                            <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="{{ $name }}" />{{ $label }}</li>
                        @endforeach
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
