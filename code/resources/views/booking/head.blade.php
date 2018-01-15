<?php

$count_products = 0;

foreach($aggregate->orders as $order) {
    $o = $order->userBooking($user->id);
    $count_products += $o->products()->count();
}

?>

@if($count_products != 0)
    <div class="row">
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            <div class="list-group pull-right">
                <a href="{{ url('booking/' . $aggregate->id . '/user/' . $user->id . '/document') }}" class="list-group-item">{{ _i('Dettaglio Consegne') }}</a>
            </div>
        </div>
    </div>

    <hr>
@endif
