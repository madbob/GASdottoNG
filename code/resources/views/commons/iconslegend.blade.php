<?php

if (!is_array($class)) {
    $class = [$class];
}

if (!isset($table_filter)) {
    $table_filter = false;
}

if (!isset($contents)) {
    $contents = null;
}

if (!isset($limit_to)) {
    $limit_to = null;
}

$icons = [];

foreach($class as $c) {
    $ico = App\GASModel::iconsLegend($c, $contents);
    $icons = array_merge($icons, $ico);
}

?>

@if(!empty($icons))
    <div class="btn-group float-end {{ $table_filter ? 'table-' : '' }}icons-legend" role="group" data-list-target="{{ $target }}">
        @foreach($icons as $icon => $label)
            @if($limit_to == null || in_array($icon, $limit_to))
                @if(is_string($label))
                    <button type="button" class="btn btn-info">
                        <i class="bi-{{ $icon }}"></i>&nbsp;
                        <span>{{ $label }}</span>
                    </button>
                @else
                    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi-{{ $icon }}"></i>&nbsp;<span>{{ $label->label }}</span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($label->items as $subicon => $sublabel)
                            <li><a href="#" class="dropdown-item"><i class="bi-{{ $subicon }}"></i>&nbsp;{{ $sublabel }}</a></li>
                        @endforeach
                    </ul>
                @endif
            @endif
        @endforeach
    </div>
@endif
