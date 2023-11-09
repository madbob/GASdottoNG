<?php $domid = Illuminate\Support\Str::random(10) ?>

<div class="row">
    <div class="col-12 col-md-6 order-2 order-md-1">
        <x-filler :data-action="route('movements.index')" :data-fill-target="sprintf('#movements-in-range-%s', $domid)">
            @include('commons.genericdaterange')
            <input type="hidden" name="generic_target_id" value="{{ $target->id }}">
            <input type="hidden" name="generic_target_type" value="{{ get_class($target) }}">
        </x-filler>
    </div>
    <div class="col-12 col-md-3 offset-md-3 order-1 order-md-2 mb-2 current-balance">
        @include('movement.status', ['obj' => $target])
    </div>
</div>

<hr>

<div class="row">
    <div class="col" id="movements-in-range-{{ $domid }}">
        <?php

        $startdate = date('Y-m-d', strtotime('-1 months'));
        $enddate = date('Y-m-d');
        $movements = $target->queryMovements(null)->where('registration_date', '>=', $startdate)->where('registration_date', '<=', $enddate)->get();

        ?>

        @include('movement.bilist', ['movements' => $movements, 'main_target' => $target])
    </div>
</div>
