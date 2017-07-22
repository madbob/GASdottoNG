<?php

if (isset($defaults_now) == false)
    $defaults_now = false;

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <input type="text"
                class="date form-control"
                name="{{ $prefix . $name }}"

                value="<?php
                    $current_value = '';

                    if ($obj && $obj->$name != null && $obj->$name != '0000-00-00')
                        $current_value = $obj->printableDate($name);

                    if (empty($current_value) && $defaults_now)
                        $current_value = ucwords(strftime('%A %d %B %G', time()));

                    echo $current_value;
                ?>"

                @if(isset($mandatory) && $mandatory == true)
                    required
                @endif

                @if($squeeze == true)
                    placeholder="{{ $label }}"
                @endif

                @if(!empty($extras))
                    @foreach ($extras as $extra_key => $extra_value)
                        {{ $extra_key }}='{{ $extra_value }}'
                    @endforeach
                @endif

                autocomplete="off">

            <div class="input-group-addon">
                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
            </div>
        </div>
    </div>
</div>
