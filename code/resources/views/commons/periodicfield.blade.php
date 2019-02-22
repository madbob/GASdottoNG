<?php

$mandatory = (isset($mandatory) && $mandatory == true);
$value = printablePeriodic(accessAttr($obj, $name));

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <input type="text"
                class="periodic form-control"
                name="{{ $prefix . $name . $postfix }}"
                value="{{ $value }}"
                onkeydown="return false"

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
