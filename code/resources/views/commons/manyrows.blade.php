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

if (isset($new_label) == false) {
    $new_label = 'Aggiungi Nuovo';
}

if (isset($show_columns) == false) {
    $show_columns = false;
}

if (isset($init_function) == false) {
    $init_function = null;
}

?>

<div class="many-rows" {!! $init_function ? 'data-init-function="' . $init_function . '"' : '' !!}>
    @if($show_columns == true)
        <div class="row many-rows-header">
            @foreach($columns as $column)
                @if($column['type'] != 'hidden')
                    <div class="col-md-{{ $column_size }} col-sm-{{ $column_size }}">
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

    @if($contents == null || $contents->isEmpty())
        <div class="row">
            @foreach($columns as $column)
                @if($column['type'] != 'custom')
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
                        <div class="col-md-{{ $column_size }} col-sm-{{ $column_size }}">
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
                        <?php

                        $names = explode(',', $column['field']);
                        $values = [];
                        foreach($names as $n)
                            $values[] = $content->$n;

                        ?>

                        <div class="col-md-{{ $column_size }} col-sm-{{ $column_size }} customized-cell">
                            {!! vsprintf($column['contents'], $values) !!}
                        </div>
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
                            <div class="col-md-{{ $column_size }} col-sm-{{ $column_size }}">
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

    <button class="btn btn-default add-many-rows">{{ $new_label }}</button>
</div>
