<?php

if ($obj == null) {
    $obj = $default;
}

$rand = rand();

if(!isset($to_modal))
    $to_modal = [];

$to_modal['dom_id'] = $rand;

?>

<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>

    <div class="col-sm-{{ $fieldsize }}">
        <label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="printable_text">
            @if (!$obj)
                {{ _i('Mai') }}
            @else
                {!! $obj->printableName() !!}
            @endif
        </label>

        <div class="pull-right">
            <input type="hidden" name="{{ $name }}" value="{{ $obj ? $obj->id : '' }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editMovement-{{ $rand }}">
                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
            </button>
        </div>

        @push('postponed')
            @include('movement.modal', $to_modal)
        @endpush
    </div>
</div>
