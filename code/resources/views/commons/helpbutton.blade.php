<?php

$class = 'btn btn-light btn-xs';

if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

if (!isset($placement)) {
    $placement = 'auto';
}

?>

@if(!empty($help_popover))
    <button type="button" class="{{ $class }}" data-container="body" data-toggle="popover" data-content="{{ $help_popover }}" data-placement="{{ $placement }}" data-html="true">
        <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
    </button>
@endif
