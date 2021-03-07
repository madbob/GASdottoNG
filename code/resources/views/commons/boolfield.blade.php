<?php

if (!isset($help_text)) {
    $help_text = '';
}

if(!isset($default_checked)) {
    $checked = ($obj && $obj->$name);
}
else {
    $checked = $default_checked;
}

/*
    È utile usare "valuefrom" nei casi in cui ci sono molteplici checkboxes,
    ciascuna riferita ad un determinato elemento (e.g. all'interno di un widget
    manyrows), magari usando il valore "id".
    In questo modo l'array generato dalla serializzazione del form conterrà gli
    identificativi degli elementi per cui la checkbox è stata spuntata, e sarà
    chiaro quali ce l'hanno spuntata e quali no
*/
if (isset($valuefrom) == false) {
    $valuefrom = null;
}

$class = 'checkbox';
if (isset($extra_class)) {
    $class .= ' ' . $extra_class;
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">
            @include('commons.helpbutton', ['help_popover' => $help_popover])
            {{ $label }}
        </label>
    @endif
    <div class="col-sm-{{ $fieldsize }}">
        <input type="checkbox"
            name="{{ $prefix . $name . $postfix }}"
            class="{{ $class }}"
            data-toggle="toggle"

            @if ($checked)
                checked="checked"
            @endif

            @if ($obj && $valuefrom)
                value="{{ $obj->$valuefrom }}"
            @endif

            autocomplete="off">

        @if(!empty($help_text))
            <span class="help-block">{{ $help_text }}</span>
        @endif
    </div>
</div>
