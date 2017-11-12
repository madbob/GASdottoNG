<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <input type="text"
            class="form-control number"
            name="{{ $prefix . $name }}"
            value="{{ $obj ? $obj->$name : '' }}"

            @if(isset($mandatory) && $mandatory == true)
                required
            @endif

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            autocomplete="off">
    </div>
</div>
