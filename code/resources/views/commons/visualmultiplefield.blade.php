<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            @foreach($values as $value => $info)
                <label class="btn btn-primary {{ isset($info->checked) && $info->checked ? 'active' : '' }}">
                    <input type="{{ $selection_type }}" name="{{ $name }}{{ $selection_type == 'checkbox' ? '[]' : '' }}" value="{{ $value }}" autocomplete="off" {{ isset($info->checked) && $info->checked ? 'checked' : '' }}> {{ $info->name }}
                </label>
            @endforeach
        </div>
    </div>
</div>
