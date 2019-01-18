<?php

if(!isset($default_value)) {
    $default_value = '';
}

$value = ($obj ? $obj->$name : '');
if(empty($value)) {
    $value = $default_value;
}

$wrap_class = 'form-group';
if (isset($extra_wrap_class)) {
    $wrap_class .= ' ' . $extra_wrap_class;
}

if (!isset($help_text)) {
    $help_text = '';
}

?>

<div class="{{ $wrap_class }}">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <textarea
            class="form-control"
            name="{{ $prefix . $name . $postfix }}"
            rows="5"

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            @if(isset($enforced_default))
                data-default-value="{{ $enforced_default }}"
            @endif

            @if(isset($maxlength))
                maxlength="{{ $maxlength }}"
            @endif

            autocomplete="off">{{ $value }}</textarea>

        @if(!empty($help_text))
            <span class="help-block">{!! $help_text !!}</span>
        @endif
    </div>
</div>
