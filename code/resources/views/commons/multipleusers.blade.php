<?php

$selections = [];

foreach(App\Role::orderBy('name', 'asc')->get() as $role) {
    $selections['special::role::' . $role->id] = __('texts.notifications.global_filter.roles', ['role' => $role->name]);
}

$aggregates = $currentgas->aggregates()->with('orders')->whereHas('orders', function($query) {
    $query->where('status', '!=', 'archived');
})->get();

foreach ($aggregates as $aggregate) {
    foreach($aggregate->orders as $order) {
        if ($order->status != 'archived') {
            $selections['special::order::'.$order->id] = __('texts.notifications.global_filter.orders', ['supplier' => $order->supplier->name, 'number' => $order->internal_number]);
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

<x-larastrap::select :name="$name" :label="$label" :options="$selections" multiple :value="$selected" thelp="notifications.help.visibility_by_selection" />
