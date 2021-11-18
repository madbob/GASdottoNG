<?php

$statuses = [];

foreach(\App\Order::statuses() as $identifier => $meta) {
    $statuses[$identifier] = $meta->label;
}

?>

<x-larastrap::select name="status" :label="_i('Stato')" :options="$statuses" :readonly="!(isset($editable) == false || $editable == true)" :pophelp="_i('Stato attuale dell\'ordine. Può assumere i valori:<ul><li>prenotazioni aperte: tutti gli utenti vedono l\'ordine e possono sottoporre le loro prenotazioni. Quando l\'ordine viene impostato in questo stato vengono anche inviate le email di annuncio</li><li>prenotazioni chiuse: tutti gli utenti vedono l\'ordine ma non possono aggiungere o modificare le prenotazioni. Gli utenti abilitati possono comunque intervenire</li><li>consegnato: l\'ordine appare nell\'elenco degli ordini solo per gli utenti abilitati, ma nessun valore può essere modificato né tantomeno possono essere modificate le prenotazioni</li><li>archiviato: l\'ordine non appare più nell\'elenco, ma può essere ripescato con la funzione di ricerca</li><li>in sospeso: l\'ordine appare nell\'elenco degli ordini solo per gli utenti abilitati, e può essere modificato</li></ul>')" />
