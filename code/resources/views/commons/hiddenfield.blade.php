<?php

$class = '';
if (isset($extra_class)) {
    $class = $extra_class;
}

if (isset($value) == false) {
    $value = '';
}
else {
    $obj = null;
}

?>

<input type="hidden" name="{{ $prefix . $name . $postfix }}" value="{{ $obj ? $obj->$name : $value }}" class="{{ $class }}" />
