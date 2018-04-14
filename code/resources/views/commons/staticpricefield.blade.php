<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        @include('commons.staticpricelabel', ['value' => $obj->$name])
    </div>
</div>
