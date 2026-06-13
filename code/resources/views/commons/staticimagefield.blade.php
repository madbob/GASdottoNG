@php

if (isset($valuefrom) == false) {
    $valuefrom = null;
}

@endphp

<x-larastrap::field :label="$label" :squeeze="$squeeze">
    @if($set)
        <div class="img-preview">
            <img src="{{ $obj->$valuefrom }}" alt="{{ $label }}">
        </div>
    @else
        <label class="static-label text-body-secondary">{{ __('texts.generic.no_image') }}</label>
    @endif
</x-larastrap::field>
