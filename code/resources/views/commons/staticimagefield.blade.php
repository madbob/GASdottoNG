<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

?>

<x-larastrap::field :label="$label" :squeeze="$squeeze">
    @if($obj && $valuefrom && !empty($obj->$valuefrom))
        <div class="img-preview">
            <img src="{{ $obj->$valuefrom }}" alt="{{ $label }}">
        </div>
    @else
        <label class="static-label text-body-secondary">{{ _i('Nessuna Immagine') }}</label>
    @endif
</x-larastrap::field>
