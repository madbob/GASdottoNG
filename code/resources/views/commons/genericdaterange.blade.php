<?php

if (!isset($start_date))
    $start_date = strtotime('-1 months');
if (!isset($end_date))
    $end_date = time();

?>

<div class="form-group">
    <label for="startdate" class="col-sm-{{ $labelsize }} control-label">Dal</label>
    <div class="col-sm-{{ $fieldsize }}">
        <input type="text" class="date form-control" name="startdate" value="{{ ucwords(strftime('%A %d %B %G', $start_date)) }}" required autocomplete="off">
    </div>
</div>

<div class="form-group">
    <label for="enddate" class="col-sm-{{ $labelsize }} control-label">Al</label>
    <div class="col-sm-{{ $fieldsize }}">
        <input type="text" class="date form-control" name="enddate" value="{{ ucwords(strftime('%A %d %B %G', $end_date)) }}" required autocomplete="off">
    </div>
</div>
