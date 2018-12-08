<?php

if (!is_array($class))
    $class = [$class];
if (!isset($table_filter))
    $table_filter = false;
if (!isset($contents))
    $contents = null;
if (!isset($limit_to))
    $limit_to = null;

$icons = [];

foreach($class as $c) {
    $ico = App\GASModel::iconsLegend($c, $contents);
    $icons = array_merge($icons, $ico);
}

?>

@if(!empty($icons))
    <div class="btn-group pull-right hidden-xs hidden-sm {{ $table_filter ? 'table-' : '' }}icons-legend" role="group" data-list-target="{{ $target }}">
        @foreach($icons as $icon => $label)
            @if($limit_to == null || in_array($icon, $limit_to))
                @if(is_string($label))
                    <button type="button" class="btn btn-info"><span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>&nbsp;{{ $label }}</button>
                @else
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                            <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>&nbsp;{{ $label->label }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @foreach($label->items as $subicon => $sublabel)
                                <li><a href="#"><span class="glyphicon glyphicon-{{ $subicon }}" aria-hidden="true"></span>&nbsp;{{ $sublabel }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        @endforeach
    </div>
@endif
