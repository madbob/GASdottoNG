<?php

if (is_null($obj)) {
    $obj = $default;
}

if (!isset($squeeze)) {
    $squeeze = false;
}
else {
    if ($squeeze) {
        $label = '';
    }
}

$rand = rand();

if (!isset($to_modal)) {
    $to_modal = [];
}

$to_modal['dom_id'] = $rand;

?>

<x-larastrap::field :pophelp="$help_popover" :label="$label" :squeeze="$squeeze">
    <label class="static-label text-body-secondary" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="name">
        @if (!$obj || $obj->exists == false)
            {{ _i('Mai') }}
        @else
            {!! $obj->printableName() !!}
        @endif
    </label>

    <div class="float-end">
        <input type="hidden" name="{{ $name }}" value="{{ $obj->id }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#editMovement-{{ $rand }}">
            <i class="bi-pencil"></i>
        </button>
    </div>
</x-larastrap::field>

@push('postponed')
    @include('movement.modal', $to_modal)
@endpush
