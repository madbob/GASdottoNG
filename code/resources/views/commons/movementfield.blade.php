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
                @if (!$obj || empty($obj->registration_date) || strstr($obj->registration_date, '0000-00-00') !== false)
                    Mai
                @else
                    {{ $obj->printableDate('registration_date') }} <span class="glyphicon {{ $obj->payment_icon }}" aria-hidden="true"></span>
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
