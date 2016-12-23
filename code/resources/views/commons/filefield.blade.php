<?php

$class = '';
if ($extra_class) {
    $class = $extra_class;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <input type="file"
            name="{{ $prefix . $name . $postfix }}"
            class="{{ $class }}"

            @if(isset($mandatory) && $mandatory == true)
                required
            @endif

            @if(!empty($extras))
                @foreach ($extras as $extra_key => $extra_value)
                    {{ $extra_key }}='{{ $extra_value }}'
                @endforeach
            @endif

            autocomplete="off">
    </div>
</div>
