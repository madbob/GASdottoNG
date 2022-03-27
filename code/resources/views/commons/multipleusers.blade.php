<?php

$selections = [];

foreach(App\Role::orderBy('name', 'asc')->get() as $role) {
    $selections['special::role::' . $role->id] = _i('Tutti gli utenti con ruolo "%s"', [$role->name]);
}

$aggregates = $currentgas->aggregates()->with('orders')->whereHas('orders', function($query) {
    $query->where('status', '!=', 'archived');
})->get();

foreach ($aggregates as $aggregate) {
    foreach($aggregate->orders as $order) {
        if ($order->status != 'archived') {
            $selections['special::order::'.$order->id] = _i("Tutti i Partecipanti all'ordine %s %s", $order->supplier->name, $order->internal_number);
        }
    }
}

foreach($currentgas->users()->whereNull('parent_id')->get() as $user) {
    $selections[$user->id] = $user->printableName();
}

if ($obj) {
    $selected = $obj->$name->pluck('id')->toArray();
}
else {
    $selected = [];
}

?>

<x-larastrap::select :name="$name" :label="$label" :options="$selections" multiple :value="$selected" :help="_i('Se nessun utente viene selezionato, l\'elemento sarà visibile a tutti.')" />
