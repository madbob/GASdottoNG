<?php

if (!isset($enforcable_change)) {
    $enforcable_change = false;
    if ($obj == null || $obj->id != $currentuser->id)
        $enforcable_change = true;
}

?>

<div class="form-group password-field">
    <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <input
                type="password"
                class="form-control password-changer {{ $enforcable_change ? 'enforcable_change' : '' }}"

                @if(is_null($obj) && isset($mandatory) && $mandatory == true)
                    required
                @endif

                @if(is_null($obj) == false)
                    placeholder="{{ _i('Lascia vuoto per non modificare la password') }}"
                @endif

                name="{{ $prefix . $name }}">

                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                </div>

                @if($enforcable_change)
                    <input type="hidden" name="enforce_password_change" value="false">
                @endif
            </div>
    </div>
</div>
