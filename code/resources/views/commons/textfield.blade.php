<?php

$class = 'form-control';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        @if(isset($postlabel))
            <div class="input-group">
        @endif

        <input type="text"
            class="{{ $class }}"
            name="{{ $prefix . $name . $postfix }}"
            value="{{ $obj ? $obj->$name : '' }}"

            @if(isset($mandatory) && $mandatory == true)
                required
            @endif

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            @if(isset($enforced_default))
                data-default-value="{{ $enforced_default }}"
            @endif

            autocomplete="off">

        @if(isset($postlabel))
            <div class="input-group-addon">{{ $postlabel }}</div>
            </div>
        @endif
    </div>
</div>
