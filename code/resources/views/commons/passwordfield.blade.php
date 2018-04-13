<?php

$class = 'form-control';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="form-group password-field">
    <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <input
                type="password"
                class="{{ $class }}"

                @if(is_null($obj) && isset($mandatory) && $mandatory == true)
                    required
                @endif

                @if($obj != null)
                    placeholder="{{ _i('Lascia vuoto per non modificare la password') }}"
                @endif

                name="{{ $prefix . $name }}">

                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                </div>
            </div>
    </div>
</div>
