<?php

$domid = str_random(10);

?>

<div class="row">
    <div class="col-md-6">
        <div class="form-horizontal form-filler" data-action="{{ url('movements') }}" data-toggle="validator" data-fill-target="#movements-in-range-{{ $domid }}">
            @include('commons.genericdaterange')
            <input type="hidden" name="generic_target_id" value="{{ $target->id }}">
            <input type="hidden" name="generic_target_type" value="{{ get_class($target) }}">

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">Ricerca</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="btn-group pull-right table-filters" data-toggle="buttons" data-table-target="#movements-in-range-{{ $domid }}">
            <label class="btn btn-info active">
                <input type="radio" name="movements-filter" value="all" autocomplete="off" checked> Tutti
            </label>
            <label class="btn btn-info">
                <input type="radio" name="movements-filter" value="credit" autocomplete="off"> Accrediti
            </label>
            <label class="btn btn-info">
                <input type="radio" name="movements-filter" value="debt" autocomplete="off"> Addebiti
            </label>
        </div>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-12" id="movements-in-range-{{ $domid }}">
        <?php

        $startdate = date('Y-m-d', strtotime('-1 months'));
        $enddate = date('Y-m-d');
        $movements = $target->queryMovements(null)->where('registration_date', '>=', $startdate)->where('registration_date', '<=', $enddate)->get()

        ?>
        @include('movement.list', ['movements' => $movements, 'main_target' => $target])
    </div>
</div>
