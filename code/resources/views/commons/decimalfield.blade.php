<?php

if (isset($fixed_value) && $fixed_value != false) {
    $value = $fixed_value;
    $disabled = true;
}
else {
    if ($obj) {
        $value = $obj->$name;
    } else {
        $value = '0';
    }

    $disabled = isset($disabled) ? $disabled : false;
}

$class = 'form-control number';

if(!isset($decimals))
    $decimals = 2;

if(isset($is_price)) {
    $value = printablePrice($value);
    $postlabel = $currentgas->currency;
    $decimals = 2;
}
else {
    $value = sprintf('%.0' . $decimals . 'f', $value);
}

$class .= ' trim-' . $decimals . '-ddigits';

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
            value="{{ $value }}"

            @if(isset($mandatory) && $mandatory == true)
                required
            @endif

            @if(isset($disabled) && $disabled == true)
                disabled
            @endif

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            autocomplete="off">

        @if(isset($postlabel))
            <div class="input-group-addon">{{ $postlabel }}</div>
            </div>
        @endif
    </div>
</div>
