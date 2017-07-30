<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <textarea
            class="form-control"
            name="{{ $prefix . $name . $postfix }}"
            rows="5"

            @if($squeeze == true)
                placeholder="{{ $label }}"
            @endif

            @if(isset($enforced_default))
                data-default-value="{{ $enforced_default }}"
            @endif

            autocomplete="off">{{ $obj ? $obj->$name : '' }}</textarea>
    </div>
</div>
