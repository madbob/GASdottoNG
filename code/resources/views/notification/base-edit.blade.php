@include('commons.textarea', ['obj' => $notification, 'name' => 'content', 'label' => 'Contenuto', 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'start_date', 'label' => 'Inizio', 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'end_date', 'label' => 'Scadenza', 'mandatory' => true])

@if($currentgas->has_mail())
    @include('commons.boolfield', ['obj' => $notification, 'name' => 'mailed', 'label' => 'Invia Mail'])
@endif

<?php

$extras['special::referrers'] = 'Tutti i Referenti';

$orders = App\Order::where('status', '!=', 'closed')->get();
foreach ($orders as $order) {
    $extras['special::order::'.$order->id] = 'Tutti i Partecipanti all\'ordine per '.$order->supplier->name;
}

?>

@include('commons.selectobjfield', [
    'obj' => $notification,
    'name' => 'users',
    'objects' => App\User::orderBy('lastname', 'asc')->get(),
    'extra_selection' => $extras,
    'multiple_select' => true,
    'label' => 'Destinatari',
    'help_text' => 'Tenere premuto Ctrl per selezionare più utenti. Se nessun utente viene selezionato, la notifica sarà destinata a tutti.'
])
