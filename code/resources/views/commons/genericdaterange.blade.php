<?php

if (!isset($start_date))
    $start_date = strtotime('-1 months');
if (!isset($end_date))
    $end_date = time();

?>

<div class="form-group">
    <label for="startdate" class="col-sm-{{ $labelsize }} control-label">{{ _i('Intervallo') }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="input-group">
            <div class="input-group-addon">{{ _i('Da') }}</div>
            <input type="text" class="date form-control" name="startdate" value="{{ ucwords(strftime('%A %d %B %G', $start_date)) }}" required autocomplete="off">
            <div class="input-group-addon">{{ _i('a') }}</div>
            <input type="text" class="date form-control" name="enddate" value="{{ ucwords(strftime('%A %d %B %G', $end_date)) }}" required autocomplete="off">
        </div>
    </div>
</div>
