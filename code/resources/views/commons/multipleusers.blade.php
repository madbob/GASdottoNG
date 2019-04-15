<?php

foreach(App\Role::orderBy('name', 'asc')->get() as $role) {
    $extras['special::role::' . $role->id] = _i('Tutti gli utenti con ruolo "%s"', [$role->name]);
}

foreach ($currentgas->aggregates()->with('orders')->whereHas('orders', function($query) { $query->where('status', '!=', 'archived'); })->get() as $aggregate) {
    foreach($aggregate->orders as $order)
        if ($order->status != 'archived')
            $extras['special::order::'.$order->id] = _i("Tutti i Partecipanti all'ordine %s %s", $order->supplier->name, $order->internal_number);
}

?>

@include('commons.selectobjfield', [
    'obj' => $obj,
    'name' => $name,
    'objects' => $currentgas->users()->whereNull('parent_id')->get(),
    'extra_selection' => $extras,
    'multiple_select' => true,
    'label' => $label,
    'help_text' => _i("Tenere premuto Ctrl per selezionare più utenti. Se nessun utente viene selezionato, l'elemento sarà visibile a tutti.")
])
