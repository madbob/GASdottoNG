<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

?>

<x-larastrap::field :label="$label" :squeeze="$squeeze">
    @if($obj && $valuefrom && !empty($obj->$valuefrom))
        <div class="img-preview">
            <img src="{{ $obj->$valuefrom }}">
        </div>
    @else
        <label class="static-label text-muted">{{ _i('Nessuna Immagine') }}</label>
    @endif
</x-larastrap::field>
