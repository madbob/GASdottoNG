<?php

if (isset($obj) && isset($name))
    $value = $obj->$name;

?>

<label class="static-label text-muted">
    {{ printablePrice($value) }} {{ $currentgas->currency }}
</label>
