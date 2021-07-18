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

<x-larastrap::modal :title="_i('Importa CSV')">
    <div class="wizard_page">
        <x-larastrap::form method="POST" :action="url('import/csv?type=' . $type . '&step=' . $next_step)" :buttons="[['color' => 'success', 'type' => 'submit', 'label' => _i('Avanti')]]">
            <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

            @foreach($extra_fields as $name => $value)
                <input type="hidden" class="wizard_field" name="{{ $name }}" value="{{ $value }}" />
            @endforeach

            <p>
                {{ _i('Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.') }}
            </p>

            @foreach($extra_description as $ed)
                <p>{{ $ed }}</p>
            @endforeach

            <hr/>

            <div class="row" id="import_csv_sorter">
                <div class="col-4">
                    <ul class="list-group">
                        @foreach($columns as $index => $column)
                            <li class="list-group-item">{{ _i('Colonna %s:', [$index + 1]) }} {{ empty($column) ? '&nbsp;' : $column }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-4">
                    <ul class="list-group">
                        @foreach($columns as $index => $column)
                            <li class="list-group-item im_droppable">{{ _i('Colonna') }} <span class="columns_index">{{ $index + 1 }}</span>: <span class="column_content"><input type="hidden" name="column[]" value="none" />{{ _i('[Ignora]') }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-4">
                    <ul class="list-group">
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="none" />{{ _i('[Ignora]') }}</li>
                        @foreach($sorting_fields as $name => $metadata)
                            <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="{{ $name }}" />
                                {{ $metadata->label }}

                                @if(isset($metadata->mandatory) && $metadata->mandatory)
                                    <strong class="text-danger"> *</strong>
                                @endif

                                @if(isset($metadata->explain))
                                    <br><small>{{ $metadata->explain }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-larastrap::form>
    </div>
</x-larastrap::modal>
