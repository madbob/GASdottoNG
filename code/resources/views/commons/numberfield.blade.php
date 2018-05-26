<?php

if (!isset($help_text)) {
    $help_text = '';
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <input type="text"
            class="form-control number"
            name="{{ $prefix . $name }}"
            value="{{ accessAttr($obj, $name, '') }}"

            @if(isset($mandatory) && $mandatory == true)
                required
            @endif

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            autocomplete="off">

        @if(!empty($help_text))
            <span class="help-block">{{ $help_text }}</span>
        @endif
    </div>
</div>
