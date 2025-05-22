<?php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

$url = $obj && $valuefrom ? $obj->$valuefrom : '';

?>

<x-larastrap::field :pophelp="$help_popover" :label="$label">
    <div class="img-preview">
        <x-larastrap::file :name="$name" :attributes="['data-max-size' => serverMaxUpload()]" squeeze="true" />

        @if(!empty($url))
            <img src="{{ $url }}" class="img-fluid" alt="{{ $label }}">
            <x-larastrap::check :name="sprintf('delete_image_%s', $name)" tlabel="generic.remove" />
        @endif
    </div>
</x-larastrap::field>
