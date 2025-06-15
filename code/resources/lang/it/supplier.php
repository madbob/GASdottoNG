<?php

return array (
  'referent' => 'Referente',
  'payment_method' => 'Modalità Pagamento',
  'all' => 'Fornitori',
  'products_list' => 
  array (
    'pdf' => 'Listino PDF (autogenerato)',
    'csv' => 'Listino CSV (autogenerato)',
  ),
  'attachments' => 'File e Immagini',
  'remote_index' => 'Indice Remoto',
  'vat' => 'Partita IVA',
  'enable_fast_shipping' => 'Abilita Consegne Veloci',
  'help' => 
  array (
    'enable_fast_shipping' => 'Quando questa opzione è abilitata, nel pannello degli ordini per questo fornitore viene attivata la tab Consegne Veloci (accanto a Consegne) che permette di marcare più prenotazioni come consegnate in un\'unica operazione',
    'enable_no_quantities' => 'Quando questa opzione è abilitata, nel pannello delle consegne per questo fornitore viene attivato un campo per immettere direttamente il valore totale della consegna anziché le quantità di ogni prodotto consegnato. Se questo campo viene usato, tutte le quantità presenti nella prenotazione si assumono essere consegnate e viene tenuto traccia della differenza del valore teorico e di quello reale immesso a mano.',
    'modifiers_notice' => 'Questi valori saranno usati come default per tutti i nuovi ordini di questo fornitore, ma sarà comunque possibile modificarli per ciascun ordine. Solo i modificatori valorizzati con qualche valore, o esplicitamente marcati come "sempre attivi", risulteranno accessibili dai relativi ordini.',
    'import_products_notice' => 'Il listino di questo fornitore è stato importato dall\'archivio centralizzato: si raccomanda si modificarlo il meno possibile in modo che sia più semplice poi gestirne gli aggiornamenti futuri.',
    'handling_products' => 'Attenzione: ci sono ordini non ancora consegnati ed archiviati per questo fornitore. Eventuali nuovi prodotti qui aggiunti o disabilitati dovranno essere abilitati o rimossi esplicitamente nell\'ordine, se desiderato, agendo sulla tabella dei prodotti.',
    'name' => 'Nome informale del fornitore',
    'legal_name' => 'Nome completo del fornitore, da usare per fini contabili e fiscali. Se non specificato, verrà usato il Nome',
    'description' => 'Breve descrizione leggibile da tutti gli utenti',
    'payment_method' => 'Eventuale nota sulle modalità di pagamento al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore',
    'orders_mode' => 'Eventuale nota sulle modalità per sottoporre gli ordini al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore',
    'send_notification_on_close' => 'Abilitando questa opzione, alla chiusura di ciascun ordine per questo fornitore la piattaforma provevderà a inoltrare automaticamente la tipologia di documento scelta. Ricorda: il Dettaglio Consegne contiene le prenotazioni divise per utente, mentre Riassunto Prodotti contiene solo le quantità complessivamente prenotate per ciascun prodotto',
  ),
  'enable_no_quantities' => 'Abilita Consegne Senza Quantità',
  'export_products' => 'Esporta Listino',
  'referents' => 'Referenti',
  'products_list_heading' => 'Listino :supplier del :date',
  'admin_categories' => 'Amministra Categorie',
  'admin_measures' => 'Amministra Unità di Misura',
  'legal_name' => 'Ragione Sociale',
  'orders_mode' => 'Modalità Avanzamento Ordini',
  'send_notification_on_close' => 'Invia notifica alla chiusura degli ordini',
);
