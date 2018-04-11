<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Stato') }}</label>

    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default {{ $target->deleted_at == null ? 'active' : '' }}">
                <input type="radio" name="status" value="active" {{ $target->deleted_at == null ? 'checked' : '' }}> {{ _i('Attivo') }}
            </label>
            <label class="btn btn-default {{ $target->suspended == true && $target->deleted_at != null ? 'active' : '' }}">
                <input type="radio" name="status" value="suspended" {{ $target->suspended == true && $target->deleted_at != null ? 'checked' : '' }}> {{ _i('Sospeso') }}
            </label>
            <label class="btn btn-default {{ $target->suspended == false && $target->deleted_at != null ? 'active' : '' }}">
                <input type="radio" name="status" value="deleted" {{ $target->suspended == false && $target->deleted_at != null ? 'checked' : '' }}> {{ _i('Cessato') }}
            </label>
        </div>
    </div>
    <div class="status-date col-sm-offset-{{ $labelsize }} col-sm-{{ $fieldsize }} {{ $target->deleted_at == null ? 'hidden' : '' }}">
        @include('commons.datefield', ['obj' => $target, 'name' => 'deleted_at', 'label' => _i('Data'), 'squeeze' => true])
    </div>
</div>
