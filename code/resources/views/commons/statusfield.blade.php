<?php

if ($target) {
    if (!is_null($target->deleted_at))
        $status = 'deleted';
    else if (!is_null($target->suspended_at))
        $status = 'suspended';
    else
        $status = 'active';
}
else {
    $status = 'active';
}

?>

<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Stato') }}</label>

    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default {{ $status == 'active' ? 'active' : '' }}">
                <input type="radio" name="status" value="active" {{ $status == 'active' ? 'checked' : '' }}> {{ _i('Attivo') }}
            </label>
            <label class="btn btn-default {{ $status == 'suspended' ? 'active' : '' }}">
                <input type="radio" name="status" value="suspended" {{ $status == 'suspended' ? 'checked' : '' }}> {{ _i('Sospeso') }}
            </label>
            <label class="btn btn-default {{ $status == 'deleted' ? 'active' : '' }}">
                <input type="radio" name="status" value="deleted" {{ $status == 'deleted' ? 'checked' : '' }}> {{ _i('Cessato') }}
            </label>
        </div>
    </div>
    <div class="status-date-deleted col-sm-offset-{{ $labelsize }} col-sm-{{ $fieldsize }} {{ $status != 'deleted' ? 'hidden' : '' }}">
        @include('commons.datefield', ['obj' => $target, 'name' => 'deleted_at', 'label' => _i('Data'), 'squeeze' => true])
    </div>
    <div class="status-date-suspended col-sm-offset-{{ $labelsize }} col-sm-{{ $fieldsize }} {{ $status != 'suspended' ? 'hidden' : '' }}">
        @include('commons.datefield', ['obj' => $target, 'name' => 'suspended_at', 'label' => _i('Data'), 'squeeze' => true])
    </div>
</div>
