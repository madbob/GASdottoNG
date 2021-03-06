<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

$url = $obj && $valuefrom ? $obj->$valuefrom : '';

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        <div class="img-preview">
            <input type="file" name="{{ $prefix . $name . $postfix }}" data-max-size="{{ serverMaxUpload() }}">
            @if(!empty($url))
                <img src="{{ $url }}" class="img-responsive">
            @endif
        </div>
    </div>
</div>
