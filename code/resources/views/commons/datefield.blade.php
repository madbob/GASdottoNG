<?php

if (isset($defaults_now) == false) {
    $defaults_now = false;
}
else {
    $enforced_default = printableDate(time());
}

$mandatory = (isset($mandatory) && $mandatory == true);

$value = printableDate(accessAttr($obj, $name, $defaults_now ? date('Y-m-d G:i:s') : ''));
if ($value == _i('Mai') && $mandatory)
    $value = '';

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">
            @include('commons.helpbutton', ['help_popover' => $help_popover])
            {{ $label }}
        </label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <input type="text"
                class="date form-control"
                name="{{ $prefix . $name . $postfix }}"
                value="{{ $value }}"
                onkeydown="return false"

                @if(isset($enforced_default))
                    data-default-value="{{ $enforced_default }}"
                @endif

                @if($mandatory)
                    required
                @endif

                @if(isset($disabled) && $disabled == true)
                    disabled
                @endif

                @if($squeeze == true)
                    placeholder="{{ $label }}"
                @endif

                @if(!empty($extras))
                    @foreach ($extras as $extra_key => $extra_value)
                        {{ $extra_key }}='{{ $extra_value }}'
                    @endforeach
                @endif

                autocomplete="off">

            <div class="input-group-addon">
                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
            </div>
        </div>
    </div>
</div>
