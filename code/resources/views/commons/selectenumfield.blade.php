<?php

$select_class = 'form-control';
if ($extra_class) {
    $select_class .= ' ' . $extra_class;
}

if (!isset($extra_attrs))
    $extra_attrs = [];

if ($obj)
    $selected_value = $obj->$name;
else if (isset($enforced_default))
    $selected_value = $enforced_default;
else
    $selected_value = null;

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <select
            class="{{ $select_class }}"
            name="{{ $prefix . $name . $postfix }}"

            @if(isset($enforced_default))
                data-default-value="{{ $enforced_default }}"
            @endif

            @foreach($extra_attrs as $attr => $val)
                {{ $attr }}="{{ $val }}"
            @endforeach

            autocomplete="off">

            @foreach($values as $v)
                <option value="{{ $v['value'] }}"
                @if ($selected_value == $v['value'])
                    selected="selected"
                @endif
                >{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>
