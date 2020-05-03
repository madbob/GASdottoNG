<?php

$columns_count = 0;
foreach ($columns as $column) {
    if ($column['type'] != 'hidden') {
        $columns_count++;
    }
}

if (isset($width)) {
    $column_size = $width;
} else {
    $column_size = floor(11 / $columns_count);
}

foreach($columns as $index => $column)
    if (!isset($columns[$index]['width']))
        $columns[$index]['width'] = $column_size;

if (isset($new_label) == false) {
    $new_label = _i('Aggiungi Nuovo');
}

if (isset($show_columns) == false) {
    $show_columns = false;
}

$class = 'many-rows';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="{{ $class }}">
    @if($show_columns == true)
        <div class="row many-rows-header">
            @foreach($columns as $column)
                @if($column['type'] != 'hidden')
                    <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }}">
                        <label>{{ $column['label'] }}</label>
                        @if(isset($column['help']))
                            <button type="button" class="btn btn-xs btn-default" data-container="body" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="{{ $column['help'] }}">
                                <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                            </button>
                        @endif
                    </div>
                @endif
            @endforeach

            <div class="col-md-12">
                <hr/>
            </div>
        </div>
    @endif

    @if(is_null($contents) || $contents->isEmpty())
        <div class="row">
            @foreach($columns as $column)
                @if($column['type'] == 'custom' && $column['field'] == 'static')
                    <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }} form-control-static">
                        {!! vsprintf($column['contents'], []) !!}
                    </div>
                @else
                    <?php

                        $attributes = [
                            'obj' => null,
                            'name' => $column['field'],
                            'label' => $column['label'],
                            'prefix' => $prefix,
                            'postfix' => '[]',
                            'squeeze' => true,
                        ];

                        if (isset($column['extra'])) {
                            $attributes = array_merge($attributes, $column['extra']);
                        }

                    ?>

                    @if($column['type'] != 'hidden')
                        <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }}">
                            @include('commons.' . $column['type'] . 'field', $attributes)
                        </div>
                    @else
                        @include('commons.' . $column['type'] . 'field', $attributes)
                    @endif
                @endif
            @endforeach
        </div>
    @else
        @foreach($contents as $content)
            <div class="row">
                @foreach($columns as $column)
                    @if($column['type'] == 'custom')
                        @if($column['field'] == 'static')
                            <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }} form-control-static">
                                {!! vsprintf($column['contents'], []) !!}
                            </div>
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

                                echo "ciao";

                            ?>

                            <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }} customized-cell form-control-static">
                                {!! vsprintf($column['contents'], $values) !!}
                            </div>
                        @endif
                    @else
                        <?php

                            $attributes = [
                                'obj' => $content,
                                'name' => $column['field'],
                                'label' => $column['label'],
                                'prefix' => $prefix,
                                'postfix' => '[]',
                                'squeeze' => true,
                            ];

                            if (isset($column['extra'])) {
                                $attributes = array_merge($attributes, $column['extra']);
                            }

                        ?>

                        @if($column['type'] != 'hidden')
                            <div class="col-md-{{ $column['width'] }} col-xs-{{ $column['width'] }}">
                                @include('commons.' . $column['type'] . 'field', $attributes)
                            </div>
                        @else
                            @include('commons.' . $column['type'] . 'field', $attributes)
                        @endif
                    @endif
                @endforeach
            </div>
        @endforeach
    @endif

    <button class="btn btn-warning add-many-rows">{{ $new_label }}</button>
</div>
