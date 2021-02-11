<?php

if (!isset($help_text)) {
    $help_text = '';
}

if(!isset($default_checked)) {
    $checked = ($obj && $obj->$name);
}
else {
    $checked = $default_checked;
}

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

$class = 'checkbox';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">
            @include('commons.helpbutton', ['help_popover' => $help_popover])
            {{ $label }}
        </label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        <input type="checkbox"
            name="{{ $prefix . $name . $postfix }}"
            class="{{ $class }}"
            data-toggle="toggle"

            @if ($checked)
                checked="checked"
            @endif

            @if ($obj && $valuefrom)
                value="{{ $obj->$valuefrom }}"
            @endif

            autocomplete="off">

        @if(!empty($help_text))
            <span class="help-block">{{ $help_text }}</span>
        @endif
    </div>
</div>
