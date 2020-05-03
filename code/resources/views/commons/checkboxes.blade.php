<?php

if (isset($obj) && is_null($obj) == false) {
    $values = as_choosable($values,
        function($i, $a) {
            return $i;
        }, function ($i, $a) {
            return $a->name;
        }, function ($i, $a) use ($obj, $name) {
            return in_array($i, accessAttr($obj, $name));
        }
    );
}

?>

@include('commons.visualmultiplefield', [
    'name' => $name,
    'label' => $label,
    'values' => $values,
    'disabled' => $disabled ?? false,
    'selection_type' => 'checkbox'
])
