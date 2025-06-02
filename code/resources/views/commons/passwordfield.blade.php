<?php

if (!isset($classes)) {
    $classes = '';
}

if (!isset($enforcable_change)) {
    $enforcable_change = false;
    if ($obj == null || $obj->id != $currentuser->id)
        $enforcable_change = true;
}

if (!isset($mandatory)) {
    $mandatory = false;
}

?>

<x-larastrap::field :label="$label" classes="password-field">
    <div class="input-group">
        <input
            type="password"
            class="form-control password-changer {{ $enforcable_change ? 'enforcable_change' : '' }} {{ $classes }}"

            @if(is_null($obj) || $mandatory == true)
                required
            @endif

            @if(is_null($obj) == false)
                placeholder="{{ __('generic.help.unchange_password') }}"
            @endif

            name="{{ $name }}">

            <div class="input-group-text">
                <i class="bi-eye-slash"></i>
            </div>

            @if($enforcable_change)
                <input type="hidden" name="enforce_password_change" value="false">
            @endif
    </div>
</x-larastrap::field>
