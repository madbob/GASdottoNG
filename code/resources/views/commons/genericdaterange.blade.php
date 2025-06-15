<?php

if (!isset($start_date)) {
    $start_date = strtotime('-1 months');
}

if (!isset($end_date)) {
    $end_date = time();
}

?>

<x-larastrap::field tlabel="generic.interval">
    <div class="input-group">
        <div class="input-group-text">{{ __('texts.generic.since') }}</div>
        <input type="text" class="date form-control" name="startdate" value="{{ $start_date ? printableDate($start_date) : '' }}" required autocomplete="off">
        <div class="input-group-text">{{ __('texts.generic.to') }}</div>
        <input type="text" class="date form-control" name="enddate" value="{{ $end_date ? printableDate($end_date) : '' }}" required autocomplete="off" data-enforce-after=".date[name=startdate]">
    </div>
</x-larastrap::field>
