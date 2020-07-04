<?php

if (isset($obj) && $obj != null && $obj->$name != null)
    $target_obj = $obj->$name;
else if (isset($target_obj))
    $target_obj = $target_obj;
else
    $target_obj = null;

$class = 'static-label';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        @if($target_obj != null)
            <label class="{{ $class }}">
                {{ $target_obj->printableName() }}
            </label>

            <div class="pull-right">
                @include('commons.detailsbutton', ['obj' => $target_obj])
            </div>
        @else
            <label class="{{ $class }}">
                {{ _i('Nessuno') }}
            </label>
        @endif
    </div>
</div>
