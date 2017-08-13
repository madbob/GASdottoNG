<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        <div class="img-preview">
            <input type="file" name="{{ $name }}">
            <img src="{{ $obj && $valuefrom ? $obj->$valuefrom : '' }}">
        </div>
    </div>
</div>
