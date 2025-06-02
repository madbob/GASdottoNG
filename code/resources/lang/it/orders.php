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
    'send_booking_summaries' => 'Questa mail verrà inviata a coloro che hanno partecipato all\'ordine ma la cui prenotazione non è ancora stata consegnata.',
    'send_delivery_summaries' => 'Questa mail verrà inviata a coloro che hanno partecipato all\'ordine e la cui prenotazione è stata effettivamente consegnata.',
    'product_selection' => 'Per abilitare o disabilitare prodotti del listino fornitore all\'interno dell\'ordine',
    'booked_modifier_column' => 'Modificatore Prodotto, sul Prenotato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell\'ordine',
    'delivered_modifier_column' => 'Modificatore Prodotto, sul Consegnato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell\'ordine',
    'fixes_column' => 'Pannello da cui modificare direttamente le quantità di prodotto in ogni prenotazione, ed aggiungere note per il fornitore',
    'number' => 'Numero progressivo automaticamente assegnato ad ogni ordine',
    'unarchived_notice' => 'Ci sono ordini chiusi da oltre un anno ma non archiviati: cercali usando la funzione di ricerca qui sotto. È raccomandato archiviare i vecchi ordini, in modo che non siano più visualizzati nella dashboard ed il caricamento delle pagine sia più veloce. Gli ordini archiviati possono comunque essere sempre recuperati con la funzione di ricerca.',
    'extimated_value' => 'Il valore qui indicato è una stima, sarà finalizzato alla chiusura dell\'ordine',
    'insufficient_credit_notice' => 'Attenzione: il tuo credito è insuffiente per effettuare nuove prenotazioni.',
    'friends_bookings_notice' => 'Da qui potrai creare delle sotto-prenotazioni assegnate ai tuoi amici. Esse andranno a far parte della tua prenotazione globale, ma potrai comunque mantenere separate le informazioni. Popola la tua lista di amici dalla pagina del tuo profilo.',
    'no_friends' => 'Non ci sono amici registrati per questo utente.',
    'closed_order_alert_new_booking' => 'Attenzione: questo ordine è stato chiuso, prima di aggiungere o modificare una prenotazione accertati che i quantitativi totali desiderati non siano già stati comunicati al fornitore o che possano comunque essere modificati.',
    'send_summaries' => 'Invia a tutti gli utenti che hanno partecipato all\'ordine una mail riassuntiva della propria prenotazione. È possibile aggiungere un messaggio da allegare a tutti, per eventuali segnalazioni addizionali. Il messaggio di riepilogo viene automaticamente inviato alla chiusura dell\'ordine, automatica o manuale che sia, se configurato dal pannello Configurazioni.',
    'automatic_instructions' => 'Con questo strumento puoi gestire apertura e chiusura automatica degli ordini. Gli ordini che vengono aperti e chiusi insieme (dunque hanno gli stessi parametri di ricorrenza, chiusura e consegna) saranno automaticamente aggregati. Quando una ricorrenza è esaurita (tutte le sue occorrenza sono date passate) viene rimossa da questo elenco.',
    'changed_products' => 'Attenzione: alcuni prodotti sono stati modificati dopo essere stati consegnati all\'interno di questo ordine. Se sono stati alterati i prezzi, è necessario rieseguire le consegne coinvolte per consolidare i nuovi totali e rielaborare i relativi movimenti contabili.',
    'waiting_closing_for_deliveries' => 'Questo pannello sarà attivo quando tutte le prenotazioni saranno chiuse',
    'modifiers_require_redistribution' => 'L\'ordine :name include modificatori il cui valore deve essere distribuito tra le prenotazioni, ed in fase di consegna tale valore è stato assegnato proporzionalmente, ma le quantità effettivamente consegnate non corrispondono a quelle prenotate e possono esserci delle discrepanze.',
    'contacts_notice' => 'Per segnalazioni relative a questo ordine si può contattare:',
    'explain_aggregations' => 'Una volta aggregati, gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l\'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.',
    'aggregation_instructions' => 'Clicca e trascina gli ordini nella stessa cella per aggregarli, o in una cella vuota per disaggregarli.',
    'status' => 'Stato attuale dell\'ordine. Può assumere i valori:<ul><li>prenotazioni aperte: tutti gli utenti vedono l\'ordine e possono sottoporre le loro prenotazioni. Quando l\'ordine viene impostato in questo stato vengono anche inviate le email di annuncio</li><li>prenotazioni chiuse: tutti gli utenti vedono l\'ordine ma non possono aggiungere o modificare le prenotazioni. Gli utenti abilitati possono comunque intervenire</li><li>consegnato: l\'ordine appare nell\'elenco degli ordini solo per gli utenti abilitati, ma nessun valore può essere modificato né tantomeno possono essere modificate le prenotazioni</li><li>archiviato: l\'ordine non appare più nell\'elenco, ma può essere ripescato con la funzione di ricerca</li><li>in sospeso: l\'ordine appare nell\'elenco degli ordini solo per gli utenti abilitati, e può essere modificato</li></ul>',
    'prices_changed' => 'I prezzi di alcuni prodotti sono cambiati rispetto alla prenotazione. Sotto, puoi scegliere quale prezzo adottare in caso di rettifica della consegna: quello applicato originariamente o quello nel listino attuale.',
    'variant_no_longer_active' => 'Nota bene: la variante selezionata in prenotazione non è più nel listino',
    'pending_saved_bookings' => 'Le quantità di alcune prenotazioni in questo ordine sono salvate ma non risultano ancora effettivamente consegnate né pagate.',
    'mail_order_notification' => 'Da questa tabella puoi attivare specifiche tipologie di notifiche mail legate agli ordini, da inviare a diversi destinatari in base allo stato di ciascun ordine.',
    'target_supplier_notifications' => 'Se questa opzione non viene abilitata, gli utenti ricevono solo le notifiche email per gli ordini dei fornitori che hanno individualmente abilitato dal proprio pannello di configurazione personale. Se viene abilitata, tutti gli utenti ricevono una notifica email ogni volta che un ordine viene aperto',
    'notify_only_partecipants' => 'La notifica viene inviata solo agli utenti che hanno partecipato all\'ordine',
    'comment' => 'Eventuale testo informativo da visualizzare nel titolo dell\'ordine. Se più lungo di :limit caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.',
    'end' => 'Data di chiusura dell\'ordine. Al termine del giorno qui indicato, l\'ordine sarà automaticamente impostato nello stato Prenotazioni Chiuse',
    'contacts' => 'I contatti degli utenti selezionati saranno mostrati nel pannello delle prenotazioni. Tenere premuto Ctrl per selezionare più utenti',
    'handle_packages' => 'Se questa opzione viene abilitata, alla chiusura dell\'ordine sarà verificato se ci sono prodotti la cui quantità complessivamente ordinata non è multipla della dimensione della relativa confezione. Se si, l\'ordine resterà aperto e sarà possibile per gli utenti prenotare solo quegli specifici prodotti finché non si raggiunge la quantità desiderata',
    'payment' => 'Da qui è possibile immettere il movimento contabile di pagamento dell\'ordine nei confronti del fornitore, che andrà ad alterare il relativo saldo',
    'no_opened' => 'Non ci sono prenotazioni aperte.',
    'no_delivering' => 'Non ci sono ordini in consegna.',
    'include_all_modifiers' => 'Usa questa funzione per includere o meno i modificatori che non sono destinati al fornitore. È consigliato selezionare \'No\' se il documento sarà inoltrato al fornitore, e \'Si\' se il documento viene usato per le consegne da parte degli addetti.',
    'supplier_multi_select' => 'Selezionando diversi fornitori, verranno generati i rispettivi ordini e saranno automaticamente aggregati. Questa funzione viene attivata se nel database sono presenti almeno 3 aggregati con almeno :theshold ordini ciascuno.',
    'start' => 'Impostando qui una data futura, e lo stato In Sospeso, questo ordine sarà automaticamente aperto nella data specificata',
    'manual_fixes_explain' => 'Da qui è possibile modificare la quantità prenotata di questo prodotto per ogni prenotazione, ma nessun utente ha ancora partecipato all\'ordine.',
    'pending_notes' => 'Alcuni utenti hanno lasciato una nota alle proprie prenotazioni.',
    'no_partecipation_notice' => 'Non hai partecipato a quest\'ordine.',
    'modifiers_notice' => 'Il valore di alcuni modificatori verrà ricalcolato quando l\'ordine sarà in stato "Consegnato".<br><a target="_blank" href="https://www.gasdotto.net/docs/modificatori#distribuzione">Leggi di più</a>',
    'no_categories' => 'Non ci sono categorie da filtrare',
    'supplier_no_orders' => 'Attualmente non ci sono ordini aperti per questo fornitore.',
    'supplier_has_orders' => 'Ci sono ordini aperti per questo fornitore',
    'unremovable_warning' => 'L\'ordine :name ha attualmente delle prenotazioni attive, e non può essere pertanto rimosso.',
    'unremovable_instructions' => 'Si raccomanda di accedere al <a href=":link">pannello delle prenotazioni per questo ordine</a> e, con lo strumento "Prenotazioni per Altri Utenti", invalidare le prenotazioni esistenti.',
    'unremovable_notice' => 'Questo meccanismo è deliberatemente non automatico e volutamente complesso, per evitare la perdita involontaria di dati.',
  ),
  'booking_description' => 
  array (
    'shipped' => 'Di seguito il riassunto dei prodotti che ti sono stati consegnati:',
    'saved' => 'Di seguito il riassunto dei prodotti che ti saranno consegnati:',
    'pending' => 'Di seguito il riassunto dei prodotti che hai ordinato:',
  ),
  'send_booking_summaries' => 'Invia Riepiloghi Prenotazioni',
  'send_delivery_summaries' => 'Invia Riepiloghi Consegne',
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
  'formatted_name' => 'da :start a :end',
  'formatted_delivery_in_name' => ', in consegna :delivery',
  'quantities' => 
  array (
    'booked' => 'Quantità Prenotata',
    'shipped' => 'Quantità Consegnata',
  ),
  'weights' => 
  array (
    'booked' => 'Peso Prenotato',
    'delivered' => 'Peso Consegnato',
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
    'manual' => 'Totale Manuale',
    'to_pay' => 'Importo da Pagare',
    'selected' => 'Totale Selezionato',
  ),
  'all' => 'Ordini',
  'statuses' => 
  array (
    'unchange' => 'Invariato',
    'to_pay' => 'Ordini da pagare',
    'open' => 'Aperto',
    'closing' => 'In Chiusura',
    'closed' => 'Chiuso',
  ),
  'do_aggregate' => 'Aggrega Ordini',
  'admin_dates' => 'Gestione Date',
  'admin_automatics' => 'Gestione Ordini Automatici',
  'notices' => 
  array (
    'closed_orders' => 'I seguenti ordini sono stati chiusi:',
    'email_attachments' => 'In allegato i relativi riassunti prodotti, in PDF e CSV.',
    'calculator' => 'Indica qui il peso dei singoli pezzi coinvolti nella consegna per ottenere la somma.',
  ),
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
  'last_summaries_date' => 'Ultime notifiche inviate',
  'aggregate' => 'Aggregato',
  'deliveries' => 'Consegne',
  'fast_deliveries' => 'Consegne Veloci',
  'modifiers_redistribution_summary' => ':name - valore definito: :defvalue / valore distribuito: :disvalue',
  'modifiers_redistribution' => 
  array (
    'keep' => 'Non fare nulla: lascia invariati i valori calcolati per i modificatori, ed i relativi addebiti ai singoli utenti, anche se la loro somma non corrisponde al valore finale atteso.',
    'recalculate' => 'Ricalcola il valore dei modificatori e ridistribuiscili in base alle consegne effettive registrate. I pagamenti avvenuti usando il Credito Utente saranno alterati, ed i relativi saldi saranno conseguentemente aggiornati; i pagamenti avvenuti con altri metodi (contanti, bonifico...) resteranno inalterati, ed eventuali aggiustamenti saranno consolidati nel saldo corrente di ciascun utente.',
  ),
  'importing' => 
  array (
    'save' => 'Assegna le quantità come salvate ma non chiudere le consegne',
    'close' => 'Marca le prenotazioni come consegnate e genera i movimenti contabili di pagamento',
  ),
  'booked_by' => 'Prenotato Da',
  'delivered_by' => 'Consegnato Da',
  'load_booked_quantities' => 'Carica Quantità Prenotate',
  'save_delivery' => 'Salva Informazioni',
  'do_delivery' => 'Consegna',
  'help_order_export_shipping' => 'Da qui puoi ottenere un documento in cui si trovano le informazioni relative alle singole prenotazioni. Utile da consultare mentre si effettuano le consegne.',
  'notify_days_before' => 'Quanti giorni prima?',
  'handle_packages' => 'Forza completamento confezioni',
  'documents' => 
  array (
    'shipping' => 
    array (
      'heading' => 'Dettaglio Consegne Ordine :identifier a :supplier del :date',
      'short_heading' => 'Dettaglio Consegne del :date',
    ),
  ),
  'list_delivering' => 'Ordini in Consegna',
  'help_aggregate_export_table' => 'Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest\'ordine.',
  'help_aggregate_export_table_for_delivery' => 'Se intendi utilizzare questo documento con la funzione \'Consegne -> Importa CSV\', per importare le quantità consegnate dopo averle elaborate manualmente, ti raccomandiamo di includere nell\'esportazione anche lo Username degli utenti coinvolti.',
  'include_all_modifiers' => 'Includi tutti i modificatori',
  'help_aggregate_export_shipping' => 'Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni di tutti gli ordini inclusi in questo aggregato.',
  'bookings_from_friends' => 'Gli ordini dei tuoi amici',
  'communications_points' => 'Per comunicazioni su quest\'ordine, si raccomanda di contattare:',
  'booking_total_amount' => 'Totale da pagare: :amount',
  'formatted_delivery_date' => 'La consegna avverrà :date.',
  'notes_to_supplier' => 'Note per il Fornitore',
  'summaries_recipients_count' => 'Utenti che riceveranno la mail: :count',
  'bookings_to_pay' => 'Prenotazioni da pagare',
  'automatic_labels' => 
  array (
    'delivery' => 'consegna',
    'days_after' => 'giorni dopo',
    'close' => 'chiudi',
    'days_before' => 'giorni prima',
    'open' => 'apri',
  ),
);