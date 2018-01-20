@include('commons.textarea', ['obj' => $notification, 'name' => 'content', 'label' => _i('Contenuto'), 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'start_date', 'label' => _i('Inizio'), 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'end_date', 'label' => _i('Scadenza'), 'mandatory' => true])
@include('commons.boolfield', ['obj' => $notification, 'name' => 'mailed', 'label' => _i('Invia Mail')])

<?php

$extras['special::referrers'] = _i('Tutti i Referenti');

$orders = App\Order::where('status', '!=', 'closed')->where('status', '!=', 'archived')->get();
foreach ($orders as $order) {
    $extras['special::order::'.$order->id] = _i("Tutti i Partecipanti all'ordine %s %s", $order->supplier->name, $order->internal_number);
}

?>

@include('commons.selectobjfield', [
    'obj' => $notification,
    'name' => 'users',
    'objects' => App\User::orderBy('lastname', 'asc')->get(),
    'extra_selection' => $extras,
    'multiple_select' => true,
    'label' => _i('Destinatari'),
    'help_text' => _i('Tenere premuto Ctrl per selezionare più utenti. Se nessun utente viene selezionato, la notifica sarà destinata a tutti.')
])
