<?php

$associated_aggregate = $gas->aggregates()->where('aggregates.id', $aggregate->id)->first();
$is_associated = $associated_aggregate != null;
$is_disabled = false;

if ($is_associated) {
    /*
        Gli ordini per i quali esistono prenotazioni da parte degli utenti del
        GAS non possono essere disassociati dal GAS stesso
    */
    foreach($associated_aggregate->orders as $order) {
        $count_bookings = $order->bookings()->withoutGlobalScopes()->whereHas('user', function($query) use ($gas) {
            $query->where('gas_id', $gas->id);
        })->count();

        if ($count_bookings != 0) {
            $is_disabled = true;
            break;
        }
    }
}

?>

<input type="checkbox" data-gas="{{ $gas->id }}" data-target-type="aggregate" data-target-id="{{ $aggregate->id }}" {{ $is_associated ? 'checked' : '' }} {{ $is_disabled ? 'disabled' : '' }}>
