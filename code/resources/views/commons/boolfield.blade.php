<?php

if (!isset($help_text)) {
    $help_text = '';
}

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        <input type="checkbox"
            name="{{ $prefix . $name . $postfix }}"
            class="checkbox"
            data-toggle="toggle"

            @if ($obj && $obj->$name == true)
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
