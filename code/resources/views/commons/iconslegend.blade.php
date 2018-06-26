<?php

if (!is_array($class))
    $class = [$class];
if (!isset($table_filter))
    $table_filter = false;

$icons = [];

foreach($class as $c) {
    $ico = App\GASModel::iconsLegend($c);
    $icons = array_merge($icons, $ico);
}

?>

@if(!empty($icons))
    <div class="btn-group pull-right hidden-xs hidden-sm {{ $table_filter ? 'table-' : '' }}icons-legend" role="group" data-list-target="{{ $target }}">
        @foreach($icons as $icon => $label)
            <button type="button" class="btn btn-info"><span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>&nbsp;{{ $label }}</button>
        @endforeach
    </div>
@endif
