<?php

if ($obj == null) {
    $obj = $default;
}

$rand = rand();

?>

<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>

    <div class="col-sm-{{ $fieldsize }}">
        <div class="col-sm-10">
            <label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="printable_text">
                @if (!$obj)
                    Mai
                @else
                    {!! $obj->printableName() !!}
                @endif
            </label>
        </div>

        @can('movements.admin', $currentgas)
            <div class="col-sm-2">
                <input type="hidden" name="{{ $name }}" value="{{ $obj->id }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editMovement-{{ $rand }}">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
            </div>

            @push('postponed')
                @include('movement.modal', ['dom_id' => $rand])
            @endpush
        @endcan
    </div>
</div>
