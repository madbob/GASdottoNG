<?php

$statuses = [];

foreach(\App\Helpers\Status::orders() as $identifier => $meta) {
    $statuses[$identifier] = $meta->label;
}

?>

<x-larastrap::select name="status" tlabel="generic.status" :options="$statuses" :readonly="!(isset($editable) == false || $editable == true)" tpophelp="orders.help.status" />
