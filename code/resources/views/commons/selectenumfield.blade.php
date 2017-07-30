<?php

$select_class = 'form-control';
if ($extra_class) {
    $select_class .= ' ' . $extra_class;
}

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

            autocomplete="off">

            @foreach($values as $v)
                <option value="{{ $v['value'] }}"
                @if ($obj && $obj->$name == $v['value'])
                    selected="selected"
                @endif
                >{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>
