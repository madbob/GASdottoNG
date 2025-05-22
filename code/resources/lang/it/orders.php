<?php

return array (
  'booking' => 
  array (
    'void' => 'Annulla Prenotazione',
    'statuses' => 
    array (
      'shipped' => 'Consegnato',
      'booked' => 'Prenotato',
      'saved' => 'Salvato',
    ),
    'nav' => 
    array (
      'mine' => 'La Mia Prenotazione',
      'friends' => 'Prenotazioni per gli Amici',
      'others' => 'Prenotazioni per Altri Utenti',
      'add' => 'Aggiungi/Modifica Prenotazione',
    ),
  ),
  'help' => 
  array (
    'pending_packages_notice' => 'Attenzione: quest\'ordine è chiuso, ma è possibile prenotare ancora alcuni prodotti per completare le confezioni da consegnare.',
    'number' => 'Numero progressivo automaticamente assegnato ad ogni ordine',
    'unarchived_notice' => 'Ci sono ordini chiusi da oltre un anno ma non archiviati: cercali usando la funzione di ricerca qui sotto. È raccomandato archiviare i vecchi ordini, in modo che non siano più visualizzati nella dashboard ed il caricamento delle pagine sia più veloce. Gli ordini archiviati possono comunque essere sempre recuperati con la funzione di ricerca.',
    'insufficient_credit_notice' => 'Attenzione: il tuo credito è insuffiente per effettuare nuove prenotazioni.',
    'friends_bookings_notice' => 'Da qui potrai creare delle sotto-prenotazioni assegnate ai tuoi amici. Esse andranno a far parte della tua prenotazione globale, ma potrai comunque mantenere separate le informazioni. Popola la tua lista di amici dalla pagina del tuo profilo.',
    'no_friends' => 'Non ci sono amici registrati per questo utente.',
    'closed_order_alert_new_booking' => 'Attenzione: questo ordine è stato chiuso, prima di aggiungere o modificare una prenotazione accertati che i quantitativi totali desiderati non siano già stati comunicati al fornitore o che possano comunque essere modificati.',
    'automatic_instructions' => 'Con questo strumento puoi gestire apertura e chiusura automatica degli ordini. Gli ordini che vengono aperti e chiusi insieme (dunque hanno gli stessi parametri di ricorrenza, chiusura e consegna) saranno automaticamente aggregati. Quando una ricorrenza è esaurita (tutte le sue occorrenza sono date passate) viene rimossa da questo elenco.',
    'contacts_notice' => 'Per segnalazioni relative a questo ordine si può contattare:',
    'pending_saved_bookings' => 'Le quantità di alcune prenotazioni in questo ordine sono salvate ma non risultano ancora effettivamente consegnate né pagate.',
    'comment' => 'Eventuale testo informativo da visualizzare nel titolo dell\'ordine. Se più lungo di :limit caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.',
    'end' => 'Data di chiusura dell\'ordine. Al termine del giorno qui indicato, l\'ordine sarà automaticamente impostato nello stato Prenotazioni Chiuse',
    'contacts' => 'I contatti degli utenti selezionati saranno mostrati nel pannello delle prenotazioni. Tenere premuto Ctrl per selezionare più utenti',
    'handle_packages' => 'Se questa opzione viene abilitata, alla chiusura dell\'ordine sarà verificato se ci sono prodotti la cui quantità complessivamente ordinata non è multipla della dimensione della relativa confezione. Se si, l\'ordine resterà aperto e sarà possibile per gli utenti prenotare solo quegli specifici prodotti finché non si raggiunge la quantità desiderata',
    'payment' => 'Da qui è possibile immettere il movimento contabile di pagamento dell\'ordine nei confronti del fornitore, che andrà ad alterare il relativo saldo',
    'no_opened' => 'Non ci sono prenotazioni aperte.',
    'no_delivering' => 'Non ci sono ordini in consegna.',
    'pending_notes' => 'Alcuni utenti hanno lasciato una nota alle proprie prenotazioni.',
    'no_partecipation_notice' => 'Non hai partecipato a quest\'ordine.',
    'modifiers_notice' => 'Il valore di alcuni modificatori verrà ricalcolato quando l\'ordine sarà in stato "Consegnato".<br><a target="_blank" href="https://www.gasdotto.net/docs/modificatori#distribuzione">Leggi di più</a>',
    'no_categories' => 'Non ci sono categorie da filtrare',
    'supplier_no_orders' => 'Attualmente non ci sono ordini aperti per questo fornitore.',
    'supplier_has_orders' => 'Ci sono ordini aperti per questo fornitore',
  ),
  'packages' => 
  array (
    'ignore' => 'No, ignora la dimensione delle confezioni',
    'permit' => 'Si, permetti eventuali altre prenotazioni',
    'permit_all' => 'Si, e contempla le quantità prenotate da parte di tutti i GAS',
  ),
  'supplier' => 'Fornitore',
  'list_open' => 'Ordini Aperti',
  'dates' => 
  array (
    'shipping' => 'Data Consegna',
    'start' => 'Data Apertura Prenotazioni',
    'end' => 'Data Chiusura Prenotazioni',
  ),
  'name' => 'Ordine',
  'quantities' => 
  array (
    'shipped' => 'Quantità Consegnata',
  ),
  'totals' => 
  array (
    'shipped' => 'Totale Consegnato',
    'total' => 'Totale',
    'taxable' => 'Totale Imponibile',
    'vat' => 'Totale IVA',
    'booked' => 'Totale Prenotato',
    'complete' => 'Totale Complessivo',
    'invoice' => 'Totale Fattura',
    'orders' => 'Totale Ordini',
    'selected' => 'Totale Selezionato',
  ),
  'all' => 'Ordini',
  'statuses' => 
  array (
    'unchange' => 'Invariato',
    'to_pay' => 'Ordini da pagare',
  ),
  'do_aggregate' => 'Aggrega Ordini',
  'admin_dates' => 'Gestione Date',
  'admin_automatics' => 'Gestione Ordini Automatici',
  'files' => 
  array (
    'aggregate' => 
    array (
      'shipping' => 'Dettaglio Consegne Aggregato',
      'summary' => 'Riassunto Prodotti Aggregato',
      'table' => 'Tabella Complessiva Aggregato',
    ),
    'order' => 
    array (
      'summary' => 'Riassunto Prodotti',
      'shipping' => 'Dettaglio Consegne',
      'table' => 'Tabella Complessiva Prodotti',
      'shipping_and_summary' => 'Dettaglio Consegne + Riassunto Prodotti',
    ),
  ),
  'help_aggregate_status' => 'Da qui puoi modificare lo stato di tutti gli ordini inclusi nell\'aggregato',
  'change_date' => 'Modifica Date',
  'help_change_date' => 'Da qui è possibile modificare la data di apertura, chiusura a consegna di tutti gli ordini inclusi nell\'aggregato',
  'aggregate' => 'Aggregato',
  'help_order_export_shipping' => 'Da qui puoi ottenere un documento in cui si trovano le informazioni relative alle singole prenotazioni. Utile da consultare mentre si effettuano le consegne.',
  'handle_packages' => 'Forza completamento confezioni',
  'list_delivering' => 'Ordini in Consegna',
  'help_aggregate_export_table' => 'Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest\'ordine.',
  'help_aggregate_export_table_for_delivery' => 'Se intendi utilizzare questo documento con la funzione \'Consegne -> Importa CSV\', per importare le quantità consegnate dopo averle elaborate manualmente, ti raccomandiamo di includere nell\'esportazione anche lo Username degli utenti coinvolti.',
  'help_aggregate_export_shipping' => 'Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni di tutti gli ordini inclusi in questo aggregato.',
);