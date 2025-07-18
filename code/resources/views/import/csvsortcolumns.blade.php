@php

if (!isset($next_step)) {
    $next_step = 'run';
}

if (!isset($extra_fields)) {
    $extra_fields = [];
}

if (!isset($extra_description)) {
    $extra_description = [];
}

@endphp

<x-larastrap::modal>
    <div class="wizard_page">
        <x-larastrap::wizardform :id="sprintf('form-%s', Illuminate\Support\Str::random(20))" :action="url('import/csv?type=' . $type . '&step=' . $next_step)">
            <input type="hidden" class="wizard_field" name="path" value="{{ $path }}" />

            @foreach($extra_fields as $name => $value)
                <input type="hidden" class="wizard_field" name="{{ $name }}" value="{{ $value }}" />
            @endforeach

            <p>{{ __('texts.imports.help.main') }}</p>

            @foreach($extra_description as $ed)
                <p>{{ $ed }}</p>
            @endforeach

            <hr/>

            <div class="row" id="import_csv_sorter">
                <div class="col-4">
                    <ul class="list-group">
                        @foreach($columns as $index => $column)
                            <li class="list-group-item">{{ __('texts.imports.index_column', ['index' => $index + 1]) }} - {{ empty($column) ? '&nbsp;' : $column }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-4">
                    <ul class="list-group">
                        @foreach($selected as $index => $sel)
                            <li class="list-group-item im_droppable">
                                {{ __('texts.imports.column') }} <span class="columns_index">{{ $index + 1 }}</span>: <span class="column_content"><input type="hidden" name="column[]" value="{{ $sel->name }}" />{{ $sel->label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-4">
                    <ul class="list-group">
                        <li class="list-group-item im_draggable"><input type="hidden" name="wannabe_column[]" value="none" />{{ __('texts.imports.ignore_slot') }}</li>

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
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
