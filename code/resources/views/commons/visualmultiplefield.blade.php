<?php

$wrap_class = 'form-group';
if (isset($extra_wrap_class)) {
    $wrap_class .= ' ' . $extra_wrap_class;
}

?>

<div class="{{ $wrap_class }}">
    @if($squeeze == false)
        <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            @foreach($values as $value => $info)
                <label class="btn btn-default {{ isset($info->checked) && $info->checked ? 'active' : '' }}">
                    <input type="{{ $selection_type }}" name="{{ $name }}{{ $selection_type == 'checkbox' ? '[]' : '' }}" value="{{ $value }}" autocomplete="off" {{ isset($info->checked) && $info->checked ? 'checked' : '' }}>
                    @if(isset($info->name))
                        {{ $info->name }}
                    @else
                        <span class="glyphicon glyphicon-{{ $info->icon }}" aria-hidden="true"></span>
                    @endif
                </label>
            @endforeach
        </div>

        @if(!empty($help_text))
            <span class="help-block">{!! $help_text !!}</span>
        @endif
    </div>
</div>
