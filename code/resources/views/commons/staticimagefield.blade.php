<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        @if($obj && $valuefrom && !empty($obj->$valuefrom))
            <div class="img-preview">
                <img src="{{ $obj->$valuefrom }}">
            </div>
        @else
            <label class="static-label text-muted">Nessuna Immagine</label>
        @endif
    </div>
</div>
