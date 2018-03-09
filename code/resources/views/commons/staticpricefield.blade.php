<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <label class="static-label text-muted">
            {{ printablePriceCurrency($obj->$name) }}
        </label>
    </div>
</div>
