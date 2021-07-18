<?php

if (isset($obj) && $obj != null && $obj->$name != null) {
    $target_obj = $obj->$name;
}
else if (isset($target_obj)) {
    $target_obj = $target_obj;
}
else {
    $target_obj = null;
}

if (!isset($label)) {
    $label = '';
    $squeeze = true;
}

$class = 'static-label';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<x-larastrap::field :label="$label" :squeeze="$squeeze">
    @if($target_obj != null)
        <label class="{{ $class }}">
            {{ $target_obj->printableName() }}
        </label>

        <div class="float-end">
            @include('commons.detailsbutton', ['obj' => $target_obj])
        </div>
    @else
        <label class="{{ $class }}">
            {{ _i('Nessuno') }}
        </label>
    @endif
</x-larastrap::field>
