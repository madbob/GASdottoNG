@php

if (isset($new_label) == false) {
    $new_label = __('generic.add_new');
}

if (isset($show_columns) == false) {
    $show_columns = false;
}

if (isset($removable_check) == false) {
    $removable_check = fn($c) => false;
}

$class = 'table table-borderless table-sm dynamic-table';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

@endphp

<table class="{{ $class }}">
    @if($show_columns == true)
        <thead>
            <tr>
                @foreach($columns as $column)
                    @if($column['type'] != 'hidden')
                        <th scope="col" {!! isset($column['width']) ? 'width="' .  $column['width']. '%"' : '' !!}>
                            {{ $column['label'] }}

                            @if(isset($column['help']))
                                <x-larastrap::pophelp :text="$column['help']" />
                            @endif
                        </th>
                    @endif
                @endforeach

                <td>
                    &nbsp;
                </td>
            </tr>
        </thead>
    @endif

    <tbody>
        @foreach($contents as $content)
            <x-larastrap::enclose :obj="$content">
                <tr>
                    @foreach($columns as $column)
                        <?php

                        $attributes = [
                            'name' => $column['field'],
                            'label' => $column['label'],
                            'nprefix' => $prefix ?? '',
                            'npostfix' => '[]',
                            'squeeze' => true,
                        ];

                        if (isset($column['extra'])) {
                            $attributes = array_merge($attributes, $column['extra']);
                        }

                        if (isset($column['extra_callback'])) {
                            $attributes = $column['extra_callback']($content, $attributes);
                        }

                        ?>

                        @if($column['type'] != 'hidden')
                            <td>
                                @if($column['type'] == 'custom')
                                    @if($column['field'] == 'static')
                                        <span class="form-control-plaintext">{!! vsprintf($column['contents'], []) !!}</span>
                                    @else
                                        <?php

                                        /*
                                            Il tipo "custom" permette di generare un contenuto
                                            arbitrario a partire da un frammento di HTML.
                                            In "contents" si aspetta una stringa formattabile in
                                            stile sprintf, con gli opportuni marcatori piazzati
                                            in giro; in "fields" si trova un elenco, separato da
                                            virgole, dei campi dell'oggetto che vogliono essere
                                            messi nella stringa di riferimento.

                                            E.g.
                                            $fields = 'name,id'
                                            $contents = 'oggetto %s ha id = %s'
                                        */

                                        $names = explode(',', $column['field']);
                                        $values = [];
                                        foreach($names as $n)
                                            $values[] = $content->$n;

                                        ?>

                                        <span class="form-control-plaintext">{!! vsprintf($column['contents'], $values) !!}</span>
                                    @endif
                                @else
                                    <x-dynamic-component :component="sprintf('larastrap::%s', $column['type'])" :params="$attributes" />
                                @endif
                            </td>
                        @else
                            <x-larastrap::hidden :params="$attributes" />
                        @endif
                    @endforeach

                    <td>
                        <div class="btn btn-danger remove-row float-end {{ $removable_check($content) ? 'disabled' : '' }}">
                            <i class="bi-x-lg"></i>
                        </div>
                    </td>
                </tr>
            </x-larastrap::enclose>
        @endforeach

        <tr>
            <td colspan="{{ count($columns) + 1 }}">
                <a href="#" class="btn btn-warning add-row">{{ $new_label }}</a>
            </td>
        </tr>
    </tbody>

    <tfoot>
        <x-larastrap::enclose :obj="null">
            <tr>
                @foreach($columns as $column)
                    <?php

                    $attributes = [
                        'name' => $column['field'],
                        'label' => $column['label'],
                        'nprefix' => $prefix ?? '',
                        'npostfix' => '[]',
                        'squeeze' => true,
                        'value' => '',
                    ];

                    if (isset($column['extra'])) {
                        $attributes = array_merge($attributes, $column['extra']);
                    }

                    ?>

                    @if($column['type'] != 'hidden')
                        <td>
                            @if($column['type'] == 'custom')
                                @if($column['field'] == 'static')
                                    <span class="form-control-plaintext">{!! vsprintf($column['contents'], []) !!}</span>
                                @endif
                            @else
                                <x-dynamic-component :component="sprintf('larastrap::%s', $column['type'])" :params="$attributes" />
                            @endif
                        </td>
                    @else
                        <x-larastrap::hidden :params="$attributes" />
                    @endif
                @endforeach

                <td>
                    <div class="btn btn-danger remove-row float-end">
                        <i class="bi-x-lg"></i>
                    </div>
                </td>
            </tr>
        </x-larastrap::enclose>
    </tfoot>
</table>
