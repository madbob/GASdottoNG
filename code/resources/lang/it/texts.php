<?php

return array (
  'aggregations' =>
  array (
    'all' => 'Aggregazioni',
    'limit_access' => 'Limita accesso',
    'help' =>
    array (
      'limit_access_to_order' => 'Selezionando uno o più elementi, l\'ordine sarà visibile solo agli utenti assegnati ai rispettivi Gruppi. Se nessun elemento viene selezionato, l\'ordine sarà visibile a tutti.',
      'permit_selection' => 'Selezionando uno o più elementi, gli utenti potranno sceglierne uno di questi in fase di prenotazione.',
      'context' => '<ul><li>Utente: i Gruppi di questa Aggregazione sono assegnabili a ciascun utente, a priori, e valgono per tutti gli Ordini. Utile per partizionare gli utenti.</li><li>Prenotazione: i Gruppi di questa Aggregazione sono assegnabili a ciascuna Prenotazione da parte degli utenti. Utile per gestire la logistica, ad esempio in caso di molteplici punti di consegna arbitrariamente selezionabili dagli utenti.</li></ul>',
      'limit_access' => 'Se selezionato, sarà possibile scegliere uno o più Gruppi di questa Aggregazione nel contesto di ogni Ordine. Così facendo, l\'Ordine stesso sarà accessibile solo agli utenti che sono stati assegnati ai Gruppi stessi.',
      'no_user_aggregations' => 'Non ci sono ancora aggregazioni assegnabili direttamente agli utenti.',
    ),
    'permit_selection' => 'Permetti selezione',
    'context' => 'Contesto',
    'by_booking' => 'Prenotazione',
    'cardinality' => 'Ogni Utente può stare in',
    'cardinality_one' => 'un solo Gruppo',
    'cardinality_many' => 'diversi Gruppi',
    'user_selectable' => 'Selezionabile dall\'Utente',
    'group' => 'Gruppo',
    'empty_list' => 'Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi sarà possibile dividere logicamente gli utenti in molteplici aggregazioni, in modo da separare le prenotazioni, organizzare la logistica delle consegne, applicare modificatori speciali e molto altro.',
    'name' => 'Aggregazione',
  ),
  'auth' =>
  array (
    'accept_privacy' => 'Ho letto e accetto l\'<a href=":link" target="_blank">Informativa sulla Privacy</a>.',
    'username' => 'Username',
    'help' =>
    array (
      'missing_user_or_mail' => 'Username o indirizzo e-mail non trovato',
      'missing_email' => 'L\'utente indicato non ha un indirizzo mail valido',
      'reset_email_notice' => 'Ti è stata inviata una mail col link per procedere all\'aggiornamento della password',
      'username_same_password' => 'La password è uguale allo username! Cambiala il prima possibile dal tuo <a class="ms-1" href=":link">pannello utente</a>!',
      'suspended_account_notice' => 'Il tuo account è stato sospeso, e non puoi effettuare prenotazioni. Verifica lo stato dei tuoi pagamenti e del tuo credito o eventuali notifiche inviate dagli amministratori.',
      'invalid_username' => 'Username non valido',
      'required_new_password' => 'Per procedere devi settare una nuova password per il tuo profilo.',
      'unconfirmed' => 'Il tuo utente non è ancora stato convalidato dagli amministratori. Quando sarà revisionato, riceverai una email di notifica.',
      'username' => 'Username col quale l\'utente si può autenticare. Deve essere univoco. Può essere uguale all\'indirizzo email',
      'email_mode' => 'Verrà inviata una email all\'utente, con cui potrà accedere la prima volta e definire la propria password.',
    ),
    'reset_username' => 'Username o indirizzo e-mail',
    'password' => 'Password',
    'password_request_link' => 'Recupero Password',
    'maintenance_notice' => 'Modalità Manutenzione: Accesso Temporaneamente Ristretto ai soli Amministratori',
    'login' => 'Login',
    'remember' => 'Ricordami',
    'register' => 'Registrati',
    'confirm_password' => 'Conferma Password',
    'update_password' => 'Aggiorna Password',
    'modes' =>
    array (
      'email' => 'Invia E-Mail',
    ),
    'failed' => 'Credenziali non valide.',
    'throttle' => 'Troppo tentativi di accesso. Riprova tra :seconds secondi.',
  ),
  'commons' =>
  array (
    'accept_conditions' => 'Ho letto e accetto le <a href=":link" target="_blank">Condizioni d\'Uso</a>.',
    'warning' => 'Attenzione',
    'loading' => 'Caricamento in corso',
    'feedback' => 'Feedback',
    'about' =>
    array (
      'opensource' => 'GASdotto è sviluppato con modello open source!',
      'contribute' => 'Puoi contribuire mandando una segnalazione o una richiesta:',
      'donate' => 'O facendo una donazione:',
      'link' => 'Puoi anche consultare <a href="https://gasdotto.net/" target="_blank">il sito di GASdotto</a> per dare una occhiata alla documentazione, o seguirci <a href="https://twitter.com/GASdottoNet" target="_blank">su Twitter</a> o <a href="https://sociale.network/@gasdottonet" target="_blank">su Mastodon</a> per aggiornamenti periodici.',
      'local_contact' => 'Attenzione: per problemi sui contenuti di questo sito (fornitori, ordini, prenotazioni...) fai riferimento agli amministrazioni del tuo GAS.',
      'translations' => 'Se vuoi contribuire alla traduzione nella tua lingua, visita <a href="https://hosted.weblate.org/projects/gasdottong/native/">questa pagina</a>.',
    ),
  ),
  'export' =>
  array (
    'help' =>
    array (
      'mandatory_column_error' => 'Colonna obbligatoria non specificata',
      'importing' =>
      array (
        'deliveries' =>
        array (
          'first_product' => 'Usa questo elemento per identificare il primo prodotto che appare nell\'elenco',
        ),
        'user' =>
        array (
          'aggregation' => 'Se specificato, deve contenere il nome di uno dei Gruppi impostati nel pannello "Configurazioni" per questa Aggregazione',
          'deleted' => 'Indicare "true" o "false"',
          'balance' => 'Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!',
          'instruction' => 'Se il login è già esistente il relativo utente sarà aggiornato coi dati letti dal file. Altrimenti verrà inviata una email di invito con il link da visitare per accedere la prima volta e definire la propria password.',
        ),
      ),
      'csv_instructions' => 'Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al soggetto. Eventuali prezzi e somme vanno espresse senza includere il simbolo dell\'euro.',
      'selection_instructions' => 'Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.',
      'img_csv_instructions' => 'Istruzioni formattazione CSV',
    ),
    'importing' =>
    array (
      'deliveries' =>
      array (
        'first_product' => 'Primo prodotto',
        'instruction' => 'Da qui puoi reimportare un CSV generato dalla funzione "Tabella Complessiva Prodotti" dell\'ordine, dopo averlo manualmente elaborato con le quantità consegnate per ogni utente.',
        'notice' => 'Nota bene: come nelle normali consegne, si assume che la quantità consegnata dei prodotti con pezzatura sia qui espressa a peso e non in numero di pezzi.',
        'product_error' => 'Prodotto non identificato: :name',
        'order_error' => 'Ordine non identificato',
        'done' => 'Consegne importate',
      ),
    ),
    'balance_csv_filename' => 'Esportazione bilancio :date.csv',
    'products_list_filename' => 'Listino :supplier.:format',
    'import' =>
    array (
      'csv' => 'Importa CSV',
      'gdxp' => 'Importa GDXP',
    ),
    'help_csv_libreoffice' => 'Per la consultazione e l\'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l\'uso di <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.',
    'data' =>
    array (
      'columns' => 'Colonne',
      'format' => 'Formato',
      'formats' =>
      array (
        'pdf' => 'PDF',
        'csv' => 'CSV',
        'gdxp' => 'GDXP',
      ),
      'status' => 'Stato Prenotazioni',
      'users' => 'Dati Utenti',
      'products' => 'Colonne Prodotti',
      'split_friends' => 'Amici separati',
    ),
    'export' =>
    array (
      'database' => 'Esporta database',
    ),
    'help_split_friends' => 'Di default, le prenotazioni degli utenti \'amici\' vengono aggregate in quelle dei rispettivi utenti principali. Selezionando \'Sì\', vengono rappresentate nel documento come prenotazioni autonome.',
    'help_aggregate_export_summary' => 'Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l\'ordine.',
    'flags' =>
    array (
      'include_unbooked' => 'Includi Prodotti non Prenotati',
    ),
    'do_balance' => 'Esporta Bilancio',
    'movements_heading' => 'Esportazione Movimenti del GAS al :date',
    'accepted_columns' => 'Le colonne ammesse per questo tipo di CSV sono:',
  ),
  'gas' =>
  array (
    'help' =>
    array (
      'csv_separator' => 'Il carattere da usare per separare i campi in tutti i CSV esportati. La scelta di questo valore dipende soprattutto dall\'applicazione che si utilizza per aprire questi files. Si consiglia l\'uso di <a target=\'_blank\' href=\'http://it.libreoffice.org/\'>LibreOffice</a>.',
      'home_message' => 'Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS o come messaggio di benvenuto',
      'currency' => 'Simbolo della valuta in uso. Verrà usato in tutte le visualizzazioni in cui sono espressi dei prezzi',
      'maintenance_mode' => 'Se abilitato, il login sarà inibito a tutti gli utenti che non hanno il permesso Accesso consentito anche in manutenzione',
      'enable_public_registration' => 'Quando questa opzione è abilitata, chiunque potrà registrarsi all\'istanza per mezzo dell\'apposito pannello (accessibile da quello di login). Gli amministratori addetti agli utenti riceveranno una mail di notifica per ogni nuovo utente registrato',
      'empty_list_shared_files' => 'Non ci sono elementi da visualizzare.<br/>I files qui aggiunti saranno accessibili a tutti gli utenti dalla dashboard: utile per condividere documenti di interesse comune.',
      'enable_deliveries_no_quantities' => 'Abilitando questa opzione, sarà possibile attivare per ogni fornitore la possibilità di effettuare le consegne specificando direttamente il valore totale della consegna anziché le quantità di ogni prodotto consegnato. Attenzione: l\'uso di questa funzione non permetterà di ottenere delle statistiche precise sui prodotti consegnati, né una ripartizione equa dei modificatori basati sulle quantità e sui pesi dei prodotti consegnati.',
      'active_columns_summary' => 'Colonne visualizzate di default nella griglia di riassunto degli ordini. È comunque sempre possibile modificare la visualizzazione dall\'interno della griglia stessa per mezzo del selettore posto in alto a destra',
      'default_columns_shipping_document' => 'Attributi selezionati di default durante l\'esportazione del Dettaglio Consegne degli ordini',
      'default_columns_shipping_split_friends' => 'Per determinare se gli amici devono essere tenuti separati o meno, di default',
      'custom_emails' => 'Da qui puoi modificare i testi delle mail in uscita da GASdotto. Per ogni tipologia sono previsti dei placeholders, che saranno sostituiti con gli opportuni valori al momento della generazione: per aggiungerli nei testi, usare la sintassi %[nome_placeholder]',
      'global_placeholders' => 'Placeholder globali, che possono essere usati in tutti i messaggi:',
      'manual_products_sorting' => 'Abilitando questa opzione, nel pannello di Modifica Rapida dei prodotti dei fornitori sarà possibile forzare un ordinamento arbitrario',
      'social_year' => 'In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate',
      'fee' => 'Se non configurato (valore = 0) non verranno gestite le quote di iscrizione',
      'deposit' => 'Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci',
      'automatic_fees' => 'Abilitando questa opzione, alla scadenza dell\'anno sociale saranno automaticamente aggiornate le quote di tutti i soci attivi, addebitandole direttamente nel credito utente.',
      'enable_sepa' => 'Abilitando questa opzione e popolando i relativi campi verrà attivata l\'esportazione dei files SEPA, con cui automatizzare le transazioni bancarie. I files saranno generabili da Contabilità -> Stato Crediti -> Esporta SEPA. Dopo aver compilato questo form, per ogni utente dovrai specificare alcuni parametri dai relativi pannelli in Utenti',
      'enable_satispay' => 'Abilitando questa opzione verranno attivati i pagamenti con Satispay, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere il codice di attivazione è necessario un account business: visita il sito https://business.satispay.com/',
      'enabled_satispay' => 'L\'integrazione Satispay risulta attualmente configurata. Disabilita Satispay e salva queste impostazioni per ricominciare la procedura di configurazione.',
      'satispay_activation_code' => 'Puoi ottenere un codice di attivazione dalla tua dashboard Satispay',
      'enable_integralces' => 'Abilitando questa opzione sarà possibile gestire la contabilità (saldi, pagamenti, movimenti...) con una moneta complementare, ed accedere ad alcune funzioni di integrazione con IntegralCES',
      'enable_invoicing' => 'Abilitando questa opzione e popolando i relativi campi verrà attivata l\'emissione delle fatture nei confronti degli utenti che effettuano prenotazioni. Le fatture saranno emesse al momento del salvataggio o della consegna della prenotazione, e saranno accessibili da Contabilità -> Fatture',
      'invoices_counter' => 'Modifica questo parametro con cautela!',
      'enable_hub' => 'Abilita alcune funzioni (sperimentali!) di integrazione con hub.economiasolidale.net, tra cui l\'aggiornamento automatico dei listini e l\'aggregazione degli ordini con altri GAS.',
      'import' => 'Da qui è possibile importare un file GDXP generato da un\'altra istanza di GASdotto o da qualsiasi altra piattaforma che supporta il formato',
      'gdxp_explain' => 'GDXP è un formato interoperabile per scambiare listini e ordini tra diversi gestionali. Da qui puoi importare un file in tale formato.',
      'multigas_mode' => 'Se abilitato, viene attivata la possibilità di amministrare molteplici GAS su questa istanza di GASdotto, che possono condividere listini e ordini',
      'only_bookings_with_credit_limit' => 'Gli utenti non possono prenotare nuovi prodotti se il loro credito diventa inferiore a questa soglia',
      'fast_product_change_columns' => 'Colonne visualizzate di default nella griglia di modifica rapida dei prodotti',
    ),
    'attribute_name' => 'Nome del GAS',
    'logo' => 'Logo Homepage',
    'home_message' => 'Messaggio Homepage',
    'language' => 'Lingua',
    'maintenance_mode' => 'Modalità Manutenzione',
    'enable_public_registration' => 'Abilita Registrazione Pubblica',
    'manual_approve_users' => 'Approvazione manuale nuovi utenti',
    'privacy_policy_link' => 'Link Privacy Policy',
    'terms_link' => 'Link Condizioni d\'Uso',
    'mandatory_fields' => 'Campi Obbligatori',
    'orders_and_deliveries' => 'Ordini e Consegne',
    'only_bookings_with_credit' => 'Permetti solo prenotazioni entro il credito disponibile',
    'enable_deliveries_no_quantities' => 'Permetti consegne manuali senza quantità',
    'display_contacts' => 'Visualizza contatti in prenotazioni',
    'active_columns_summary' => 'Colonne Riassunto Ordini',
    'default_columns_shipping_document' => 'Colonne Attive in Dettaglio Consegne',
    'suppliers_and_products' => 'Fornitori e Prodotti',
    'manual_products_sorting' => 'Permetti di riorganizzare manualmente l\'elenco dei prodotti',
    'fast_product_change_columns' => 'Colonne Modifica Rapida',
    'social_year' => 'Inizio Anno Sociale',
    'automatic_fees' => 'Addebita automaticamente quota alla scadenza dell\'anno sociale',
    'enable_sepa' => 'Abilita SEPA',
    'enable_satispay' => 'Abilita Satispay',
    'satispay' =>
    array (
      'activation_code' => 'Codice di Attivazione',
    ),
    'enable_integralces' => 'Abilita IntegralCES',
    'integralces_identifier' => 'Identificativo conto del GAS',
    'enable_invoicing' => 'Abilita Emissione Fatture',
    'invoices_counter' => 'Contatore Fatture',
    'import_export' => 'Importa/Esporta',
    'enable_hub' => 'Integrazione Hub Economia Solidale',
    'csv_separator' => 'Separatore Files CSV Esportati',
    'import' => 'Importazione',
    'multigas_mode' => 'Modalità Multi GAS',
    'only_bookings_with_credit_limit' => 'Limite di Credito',
  ),
  'generic' =>
  array (
    'save' => 'Salva',
    'create_format' => 'Crea :type',
    'empty_list' => 'Non ci sono elementi da visualizzare.',
    'add_new' => 'Aggiungi Nuovo',
    'type' => 'Tipo',
    'none' => 'Nessuno',
    'manual_selection' => 'Selezione manuale',
    'named_all' => 'Tutti :name',
    'email' => 'E-Mail',
    'phone' => 'Telefono',
    'absolute' => 'Assoluto',
    'percentage' => 'Percentuale',
    'by_weight' => 'A Peso',
    'quantity' => 'Quantità',
    'value' => 'Valore',
    'weight' => 'Peso',
    'remove' => 'Elimina',
    'export' => 'Esporta',
    'undefined' => 'indefinita',
    'updated_at_formatted' => 'Ultima Modifica: <br class="d-block d-md-none">:date - :person',
    'address' => 'Indirizzo',
    'email_no_notifications' => 'E-Mail (no notifiche)',
    'cellphone' => 'Cellulare',
    'fax' => 'Fax',
    'website' => 'Sito Web',
    'confirmed' => 'Confermato',
    'temporary' => 'Provvisorio',
    'measure' => 'Unità di Misura',
    'category' => 'Categoria',
    'price' => 'Prezzo',
    'yes' => 'Sì',
    'no' => 'No',
    'iban' => 'IBAN',
    'gas' => 'GAS',
    'status' => 'Stato',
    'unspecified' => 'Non Specificato',
    'never' => 'Mai',
    'help' =>
    array (
      'save_reminder' => 'Ricorda di cliccare il tasto "Salva" quando hai finito!',
      'preferred_date_format' => 'Preferibilmente in formato YYYY-MM-DD (e.g. :now)',
      'contacts' => 'Qui si può specificare un numero arbitrario di contatti per il soggetto. Le notifiche saranno spedite a tutti gli indirizzi e-mail indicati. Si raccomanda di specificare un solo contatto per riga.',
      'unchange_password' => 'Lascia vuoto per non modificare la password',
      'multigas_admin_instructions' => 'Ogni GAS ha i suoi utenti, e qui puoi definire le credenziali per il primo utente del nuovo GAS. Gli verrà assegnato il "Ruolo Amministratore Multi-GAS" definito nel pannello delle configurazioni dei permessi.',
      'discrete_measure' => 'Le unità discrete non sono frazionabili: sui prodotti cui viene assegnata una unità di misura etichettata con questo attributo non sarà possibile attivare proprietà come Prezzo Variabile e Pezzatura',
      'categories_instructions' => 'Clicca e trascina le categorie nell\'elenco per ordinarle gerarchicamente.',
      'insert_password_notice' => 'Per confermare questa operazione devi immettere la tua password utente',
      'unassigned_group_warning' => 'Attenzione: :count utenti non hanno un gruppi assegnato per :group',
    ),
    'definitive_delete' => 'Elimina Definitivamente',
    'all' => 'Tutti',
    'unauthorized' => 'Non autorizzato',
    'error' => 'Errore',
    'date' => 'Data',
    'number' => 'Numero',
    'taxable_amount' => 'Imponibile',
    'vat' => 'IVA',
    'payment' => 'Pagamento',
    'identifier' => 'Identificativo',
    'notes' => 'Note',
    'id' => 'ID',
    'closing_date' => 'Data Chiusura',
    'stats' =>
    array (
      'involved_orders' => 'Valore Ordini',
      'involved_users' => 'Utenti Coinvolti',
      'generic' => 'Statistiche Generali',
      'supplier' => 'Statistiche per Fornitore',
    ),
    'description' => 'Descrizione',
    'invoice' => 'Fattura',
    'no_value' => 'Nessun Valore',
    'by_kg' => 'al KG',
    'selection' => 'Selezione',
    'home' => 'Home',
    'menu' =>
    array (
      'bookings' => 'Prenotazioni',
      'accounting' => 'Contabilità',
      'stats' => 'Statistiche',
      'notifications' => 'Notifiche',
      'configs' => 'Configurazioni',
      'multigas' => 'Multi-GAS',
      'friends' => 'Amici',
      'receipts' => 'Ricevute',
    ),
    'image' => 'Immagine',
    'limited_access' => 'Accesso limitato',
    'disabled' => 'Disabilitato',
    'kilos' => 'Chili',
    'sortings' =>
    array (
      'all_by_user' => 'Tutti (ordinati per utente)',
      'manual' => 'Ordinamento Manuale',
      'all_by_group' => 'Tutti (ordinati per gruppo)',
    ),
    'minor_than' => 'Minore di',
    'major_than' => 'Maggiore di',
    'exports' =>
    array (
      'csv' => 'Esporta CSV',
      'integralces' => 'Esporta IntegralCES',
      'sepa' => 'Esporta SEPA',
      'pdf' => 'Esporta PDF',
    ),
    'change' => 'Modifica',
    'details' => 'Dettagli',
    'photo' => 'Foto',
    'composable_all' => 'Tutti :label',
    'attachments' =>
    array (
      'replace_file' => 'Sostituisci File',
      'view' => 'Visualizza o Scarica',
      'replace_url' => 'Sostituisci URL',
    ),
    'recipients' => 'Destinatari',
    'click_here' => 'Clicca Qui',
    'attachment' => 'Allegato',
    'contacts' => 'Contatti',
    'errors' => 'Errori',
    'search' =>
    array (
      'users' => 'Cerca Utente',
      'all' => 'Ricerca',
    ),
    'comment' => 'Commento',
    'interval' => 'Intervallo',
    'since' => 'Da',
    'to' => 'a',
    'method' => 'Metodo',
    'automatic' => 'Automatico',
    'related' => 'Correlati',
    'more' => 'Altro',
    'send_mail' => 'Invia Mail',
    'optional_message' => 'Messaggio (Opzionale)',
    'default_notes' => 'Note di Default',
    'default' => 'Default',
    'suspend' => 'Sospendi',
    'created_at' => 'Data Creazione',
    'updated_at' => 'Ultima Modifica',
    'multigas_name' => 'Nome del nuovo GAS',
    'how_to_proceed' => 'Come vuoi procedere?',
    'create' => 'Crea nuovo',
    'targets' => 'Oggetti',
    'suppliers_and_orders' => 'Fornitori/Ordini',
    'mailfield' =>
    array (
      'subject' => 'Soggetto',
      'body' => 'Testo',
    ),
    'reference' => 'Riferimento',
    'measures' =>
    array (
      'discrete' => 'Unità Discreta',
    ),
    'do_filter' => 'Filtra',
    'close' => 'Chiudi',
    'save_and_proceed' => 'Salva e Procedi',
    'behavior' => 'Comportamento',
    'uppercare_gas_heading' => 'GRUPPO DI ACQUISTO SOLIDALE',
    'confirm' => 'Conferma',
    'delete_confirmation' => 'Sei sicuro di voler eliminare questo elemento?',
    'current_gas_name' => 'GAS attivo: :label',
    'shared_files' => 'File Condivisi',
    'file' => 'File',
    'logs' => 'Log',
    'message' => 'Messaggio',
    'values' => 'Valori',
    'no_image' => 'Nessuna Immagine',
    'finished_operation' => 'Operazione conclusa.',
    'before' => 'Prima',
    'after' => 'Dopo',
    'sort_by' => 'Ordina Per',
    'view_all' => 'Vedi Tutti',
    'update' => 'Aggiorna',
    'fast_modify' => 'Modifica Rapida',
    'download' => 'Scarica',
    'split' => 'di cui',
    'start' => 'Inizio',
    'expiration' => 'Scadenza',
    'do_duplicate' => 'Duplica',
    'action' =>
    array (
      'ignore' => 'Ignora',
      'disable' => 'Disabilita',
    ),
    'operation' => 'Operazione',
    'sum' => 'Somma',
    'sub' => 'Sottrazione',
    'passive' => 'Passivo',
    'apply' => 'Applica',
    'difference' => 'Differenza',
    'theshold' => 'Soglia',
    'cost' => 'Costo',
    'forward' => 'Inoltra',
    'do_not_modify' => 'Non Modificare',
    'named_multigas' => 'Multi-GAS: :name',
    'categories' => 'Categorie',
    'no_data' => 'Non ci sono dati da visualizzare',
    'name' => 'Nome',
    'url' => 'URL',
    'only_selected' => 'Solo selezionati',
    'subject' => 'Soggetto',
    'aggregations_and_groups' => 'Aggregazioni/Gruppi',
    'select' => 'Seleziona',
    'to_do' => 'Azione',
    'opening' => 'Apertura',
    'closing' => 'Chiusura',
    'mandatory' => 'Obbligatorio',
    'waiting' => 'In Attesa',
  ),
  'imports' =>
  array (
    'help' =>
    array (
      'new_remote_products_list' => 'Nuovo aggiornamento disponibile per il listino :supplier (:date). Consultalo dal pannello Fornitori -> Indice Remoto.',
      'failed_file' => 'File non caricato correttamente',
      'failure_notice' => 'Importazione fallita',
      'invalid_command' => 'Comando :type/:step non valido',
      'currency_id' => 'Una delle valute gestite dal sistema. Se non specificato, verrà selezionata quella di default(:default). Valori ammessi: :values',
      'unique_user_id' => 'Gli utenti sono identificati per username o indirizzo mail (che deve essere univoco!).',
      'no_user_found' => 'Utente non trovato: :name',
      'no_supplier_found' => 'Fornitore non trovato: :name',
      'no_currency_found' => 'Valuta non trovata: :name',
      'imported_movements_notice' => 'Movimenti importati',
      'main' => 'Clicca e trascina gli attributi dalla colonna di destra alla colonna centrale, per assegnare ad ogni colonna del tuo file un significato.',
      'remote_index' => 'Questa funzione permette di accedere e tenere automaticamente aggiornati i listini condivisi su :url. Attenzione: è una funzione sperimentale, usare con cautela!',
    ),
    'ignore_slot' => '[Ignora]',
    'name_or_vat' => 'Nome o partita IVA',
    'imported_users' => 'Utenti importati',
    'do' => 'Importa',
    'update_supplier' => 'Aggiorna fornitore esistente',
    'select_supplier' => 'Seleziona un fornitore',
    'products_count' => 'Nel file ci sono :count prodotti.',
    'index_column' => 'Colonna :index',
    'column' => 'Colonna',
    'imported_suppliers' => 'Fornitori importati',
    'updated' => 'Aggiornato',
    'last_read' => 'Ultima Lettura',
    'error_main' => 'Errore nel caricamento o nella lettura del file.',
    'error_retry' => 'Si prega di riprovare, o di sottoporre il problema agli sviluppatori di GASdotto: info@madbob.org',
    'existing_products_action' => 'Prodotti Esistenti',
    'no_products' => 'Nessun Prodotto Aggiornabile',
  ),
  'invoices' =>
  array (
    'waiting' => 'In Attesa',
    'statuses' =>
    array (
      'to_verify' => 'Da Verificare',
      'verified' => 'Verificata',
      'payed' => 'Pagata',
    ),
    'default_note' => 'Pagamento fattura :name',
    'documents' =>
    array (
      'invoice' =>
      array (
        'heading' => 'Fattura :identifier',
      ),
      'receipts' =>
      array (
        'list_filename' => 'Esportazione ricevute GAS :date.csv',
      ),
    ),
    'balances' =>
    array (
      'supplier' => 'Saldo Fornitore',
    ),
    'forwarded' => 'Inoltrata',
    'orders' => 'Ordini Coinvolti',
    'help' =>
    array (
      'orders' => 'Seleziona gli ordini che sono coinvolti in questa fattura. Quando la fatturà sarà marcata come pagata, ad essi sarà aggiunto il riferimento al movimento contabile di pagamento e saranno automaticamente archiviati',
      'no_orders' => 'Non ci sono ordini assegnabili a questa fattura. Gli ordini devono: fare riferimento allo stesso fornitore cui è assegnata la fattura; non avere un pagamento al fornitore già registrato; essere in stato "Consegnato" o "Archiviato"; avere almeno una prenotazione "Consegnata" (il totale delle prenotazioni consegnate viene usato per effettuare il calcolo del pagamento effettivo).',
      'filtered_orders' => 'Qui appaiono gli ordini che: appartengono al fornitore intestatario della fattura; sono in stato Consegnato o Archiviato; hanno almeno una prenotazione marcata come Consegnata. I totali vengono calcolati sulle quantità effettivamente consegnate, non sulle prenotazioni.',
    ),
    'change_orders' => 'Modifica Ordini',
    'verify' => 'Verifica Contenuti',
    'other_modifiers' => 'Altri modificatori non destinati a questa fattura:',
    'payment' => 'Registra Pagamento',
    'get_or_send' => 'Scarica o Inoltra',
    'new' => 'Carica Nuova Fattura',
    'send_pending_receipts' => 'Inoltra Ricevute in Attesa',
    'shipping_of' => 'Consegna: :date',
  ),
  'mail' =>
  array (
    'help' =>
    array (
      'removed_email_log' => 'Rimosso indirizzo email :address',
      'send_error' => 'Impossibile inoltrare mail a :email: :message',
    ),
    'summary' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Riassunto prenotazione del GAS: :supplier - consegna :delivery',
      ),
    ),
    'closed' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Ordini chiusi automaticamente',
      ),
    ),
    'notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nuova notifica da :gas',
      ),
    ),
    'new_user_notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nuovo utente registrato',
      ),
    ),
    'contacts_prefix' => 'Per informazioni: :contacts',
    'approved' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Benvenuto!',
        'body' => 'Benvenuto in %[gas_name]!
    In futuro potrai accedere usando il link qui sotto, lo username "%[username]" e la password da te scelta.
    %[gas_login_link]',
      ),
      'description' => 'Messaggio inviato agli iscritti approvati.',
      'username' => 'Username assegnato al nuovo utente',
      'link' => 'Link della pagina di login',
    ),
    'declined' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Non sei stato approvato!',
        'body' => 'Spiacente, ma il tuo account non è stato approvato da %[gas_name].',
      ),
      'description' => 'Messaggio inviato agli iscritti non approvati.',
    ),
    'order' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nuovo Ordine Aperto per %[supplier_name]',
        'body' => 'È stato aperto da %[gas_name] un nuovo ordine per il fornitore %[supplier_name].
    Per partecipare, accedi al seguente indirizzo:
    %[gas_booking_link]
    Le prenotazioni verranno chiuse %[closing_date]',
      ),
      'description' => 'Notifica per i nuovi ordini aperti (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).',
      'comment' => 'Testo di commento dell\'ordine',
      'link' => 'Link per le prenotazioni',
      'mails' => 'Indirizzi email dei referenti dell\'ordine',
    ),
    'reminder' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Ordini in chiusura per %[gas_name]',
        'body' => 'Tra pochi giorni si chiuderanno gli ordini aperti da %[gas_name] per i seguenti fornitori:

    %[orders_list]',
      ),
      'description' => 'Notifica di promemoria per gli ordini in chiusura (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).',
      'list' => 'Elenco degli ordini in chiusura',
      'suppliers' => 'Elenco dei fornitori',
    ),
    'password' =>
    array (
      'defaults' =>
      array (
        'body' => 'È stato chiesto l\'aggiornamento della tua password su GASdotto.
    Clicca il link qui sotto per aggiornare la tua password, o ignora la mail se non hai chiesto tu questa operazione.
    %[gas_reset_link]',
      ),
      'description' => 'Messaggio per il ripristino della password.',
      'link' => 'Link per il reset della password',
    ),
    'receipt' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nuova fattura da %[gas_name]',
        'body' => 'In allegato l\'ultima fattura da %[gas_name]',
      ),
      'description' => 'Mail di accompagnamento per le ricevute.',
    ),
    'supplier' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Prenotazione ordine %[gas_name]',
        'body' => 'Buongiorno.
    In allegato trova - in duplice copia, PDF e CSV - la prenotazione dell\'ordine da parte di %[gas_name].
    Per segnalazioni, può rivolgersi ai referenti in copia a questa mail.
    Grazie.',
      ),
      'description' => 'Notifica destinata ai fornitori alla chiusura automatica dell\'ordine.',
    ),
    'credit' =>
    array (
      'current' => 'Credito corrente dell\'utente',
    ),
    'welcome' =>
    array (
      'description' => 'Messaggio inviato ai nuovi utenti creati sulla piattaforma.',
      'link' => 'Link per accedere la prima volta',
      'defaults' =>
      array (
        'body' => 'Sei stato invitato a %[gas_name]!

    Per accedere la prima volta clicca il link qui sotto.
    %[gas_access_link]

    In futuro potrai accedere usando quest\'altro link, lo username "%[username]" e la password che avrai scelto.
    %[gas_login_link]
    ',
      ),
    ),
    'newuser' =>
    array (
      'description_manual' => 'Messaggio inviato ai nuovi iscritti registrati sulla piattaforma, in attesa di approvazione.',
      'description' => 'Messaggio inviato ai nuovi iscritti registrati sulla piattaforma.',
    ),
  ),
  'modifiers' =>
  array (
    'defaults' =>
    array (
      'discount' => 'Sconto',
      'rounding' => 'Arrotondamento Consegna Manuale',
      'delivery' => 'Spese Trasporto',
    ),
    'dynamics' =>
    array (
      'values' =>
      array (
        'quantity' => 'la quantità',
        'price' => 'il valore',
        'order_price' => 'il valore dell\'ordine',
        'weight' => 'il peso',
      ),
      'targets' =>
      array (
        'product' =>
        array (
          'booking' => 'di prodotto nella prenotazione',
          'order' => 'di prodotto nell\'ordine',
        ),
        'order' =>
        array (
          'booking' => 'della prenotazione',
          'order' => 'dell\'ordine',
        ),
        'aggregate' =>
        array (
          'booking' => 'della prenotazione aggregata',
          'order' => 'dell\'ordine aggregato',
        ),
        'circle' =>
        array (
          'booking' => 'della prenotazione assegnata al gruppo',
          'order' => 'della porzione di ordine destinata al gruppo',
        ),
      ),
      'scale' =>
      array (
        'minor' => 'è minore di',
        'major' => 'è maggiore di',
      ),
      'distribution' =>
      array (
        'sum' =>
        array (
          'product' => 'somma al costo del prodotto',
          'booking' => 'somma al costo della prenotazione',
          'order' => 'somma al costo dell\'ordine',
          'product_kg' => 'per ogni Kg di prodotto, somma',
          'booking_kg' => 'per ogni Kg nella prenotazione, somma',
          'order_kg' => 'per ogni Kg nell\'ordine, somma',
        ),
        'sub' =>
        array (
          'product' => 'sottrai al costo del prodotto',
          'booking' => 'sottrai al costo della prenotazione',
          'order' => 'sottrai al costo dell\'ordine',
          'product_kg' => 'per ogni Kg di prodotto, sottrai',
          'booking_kg' => 'per ogni Kg nella prenotazione, sottrai',
          'order_kg' => 'per ogni Kg nell\'ordine, sottrai',
        ),
        'passive' =>
        array (
          'product' => 'rispetto al costo del prodotto, calcola',
          'booking' => 'rispetto al costo della prenotazione, calcola',
          'order' => 'rispetto al costo dell\'ordine, calcola',
          'product_kg' => 'per ogni Kg di prodotto, calcola',
          'booking_kg' => 'per ogni Kg nella prenotazione, calcola',
          'order_kg' => 'per ogni Kg nell\'ordine, calcola',
        ),
        'apply' =>
        array (
          'product' => 'applica il prezzo unitario',
        ),
      ),
      'types' =>
      array (
        'quantity' => 'e distribuiscilo in base alle quantità prenotate',
        'price' => 'e distribuiscilo in base al valore delle prenotazioni',
        'weight' => 'e distribuiscilo in base al peso delle prenotazioni',
      ),
      'template' => 'Se :value :target :scale',
    ),
    'all' => 'Modificatori',
    'name' => 'Modificatore',
    'help' =>
    array (
      'no_modifiers_for_element' => 'Non ci sono modificatori assegnabili a questo tipo di elemento.',
    ),
  ),
  'movements' =>
  array (
    'modifier_no_theshold' => 'Nessuna soglia',
    'order_value' => 'Valore dell\'Ordine',
    'apply_to_booking' => 'Singola Prenotazione',
    'apply_to_order' => 'Ordine Complessivo',
    'current_balance_amount' => 'Saldo Attuale: :amount',
    'balance' => 'Saldo',
    'current_credit' => 'Credito Attuale',
    'bank_account' => 'Conto Corrente',
    'cash_account' => 'Cassa Contanti',
    'deposits' => 'Cauzioni',
    'documents' =>
    array (
      'movements' =>
      array (
        'filename' => 'Esportazione movimenti GAS :date.:format',
      ),
      'users' =>
      array (
        'filename' => 'Crediti al :date.csv',
        'integralces_filename' => 'IntegralCES Utenti.csv',
      ),
      'sepa' =>
      array (
        'filename' => 'SEPA del :date.xml',
      ),
      'suppliers' =>
      array (
        'filename' => 'Saldi Fornitori al :date.csv',
        'integralces_filename' => 'IntegralCES Fornitori.csv',
      ),
      'balances' =>
      array (
        'filename' => 'Storico Saldi al :date.csv',
      ),
    ),
    'registration_date' => 'Data Registrazione',
    'execution_date' => 'Data Movimento',
    'paying' => 'Pagante',
    'payed' => 'Pagato',
    'delete_confirmation' => 'Vuoi davvero eliminare il movimento<br>:name?',
    'formatted_residual_credit' => 'Credito Residuo :currency',
    'formatted_balance' => 'Saldo :currency',
    'currency' => 'Valuta',
    'credit' => 'Credito',
    'defaults' =>
    array (
      'fee' => 'Versamento della quota annuale da parte di un socio',
      'booking' => 'Pagamento prenotazione da parte di un socio',
      'booking_adjust' => 'Aggiustamento pagamento prenotazione da parte di un socio',
      'deposit' => 'Deposito cauzione socio del GAS',
      'deposit_return' => 'Restituzione cauzione socio del GAS',
      'donation_from' => 'Donazione dal GAS',
      'donation' => 'Donazione al GAS',
      'expense' => 'Acquisto/spesa GAS',
      'put' => 'Versamento sul conto',
      'invoice' => 'Pagamento fattura a fornitore',
      'order' => 'Pagamento ordine a fornitore',
      'rounding' => 'Arrotondamento/sconto fornitore',
      'credit' => 'Deposito di credito da parte di un socio',
      'decredit' => 'Reso credito per un socio',
      'refund' => 'Rimborso spesa socio',
    ),
    'methods' =>
    array (
      'bank' => 'Bonifico',
      'cash' => 'Contanti',
      'credit' => 'Credito Utente',
      'sepa' => 'SEPA',
    ),
    'formatted_revenues' => 'Entrate :name',
    'formatted_expenses' => 'Uscite :name',
    'suppliers_status' => 'Stato Fornitori',
    'causal' => 'Causale',
    'generic_causal' => 'Versamento GAS',
    'help' =>
    array (
      'removing_balance_warning' => 'Attenzione! I saldi passati possono essere rimossi ma con prudenza, l\'operazione non è reversibile, e non sarà più possibile ricalcolare questi valori in nessun modo!',
      'missing_method_for_movement' => 'Attenzione! Nessun metodo di pagamento è attivo per questo tipo di movimento (:name)! Si raccomanda di modificare le impostazioni nel pannello Contabilità -> Tipi Movimenti, o non sarà possibile salvare correttamente questo movimento!',
      'accepts_negative_value' => 'Se disabilitato, impedisce di immettere un ammontare negativo per il movimento contabile',
      'fixed_value' => 'Se diverso da 0, non sarà possibile modificare il valore dei nuovi movimenti di questo tipo',
      'paying' => 'Il tipo di entità che effettua il pagamento. Se selezionato, sarà possibile selezionare l\'entità all\'interno del pannello di creazione di un nuovo movimento',
      'payed' => 'Il tipo di entità che riceve il pagamento. Se selezionato, sarà possibile selezionare l\'entità all\'interno del pannello di creazione di un nuovo movimento',
      'system_type_notice' => 'Questo è un tipo di movimento contabile indispensabile per il funzionamento del sistema: non può essere eliminato e può essere modificato solo parzialmente.',
      'empty_list_vat_rates' => 'Non ci sono elementi da visualizzare.<br/>Le aliquote potranno essere assegnate ai diversi prodotti nei listini dei fornitori, e vengono usate per scorporare automaticamente l\'IVA dai totali delle fatture caricate in <strong>Contabilità -> Fatture</strong>.',
      'balances_diff' => 'I seguenti saldi sono risultati diversi al termine del ricalcolo.',
      'balances_same' => 'Tutti i saldi risultano coerenti.',
      'archiviation_notice' => 'È raccomandato archiviare i saldi periodicamente, ad esempio alla chiusura dell\'anno sociale, dopo aver verificato che questi siano tutti corretti. In tal modo le successive operazioni di ricalcolo saranno molto più veloci, non dovendo computare ogni volta tutti i movimenti contabili esistenti ma solo quelli avvenuti dopo l\'ultima archiviazione. I movimenti archiviati saranno comunque sempre consultabili. Questa operazione può richiedere diversi minuti per essere completata.',
      'opened_orders_with_modifier' => 'Ci sono ordini non ancora consegnati ed archiviati per questo fornitore, che non hanno attivato il modificatore appena modificato. Seleziona gli ordini per i quali vuoi attivare questo modificatore (o clicca \'Chiudi\' per non attivarlo su nessuno).',
      'main_types_warning' => 'Attenzione! Modifica i comportamenti dei tipi di movimento contabile con molta cautela! Prima di modificare il comportamento di un tipo esistente, e magari già assegnato a qualche movimento contabile registrato, si raccomanda di usare la funzione "Archivia Saldi" in modo che i movimenti precedentemente contabilizzati non vengano rielaborati usando il nuovo comportamento (producendo saldi completamente diversi da quelli attuali).',
      'modifier_not_applied_in_time_range' => 'Il modificatore non è stato applicato in questo intervallo di date.',
      'current_balance' => 'Questo è il tuo saldo attuale nei confronti del GAS.',
      'pending_bookings_to_pay' => 'Questo è il totale delle tue prenotazioni non ancora consegnate, e di cui non è dunque ancora stato registrato il pagamento.',
      'always_active_modifiers' => 'Se attivo, il modificatore viene sempre incluso nei nuovi ordini per questo fornitore anche se non viene qui valorizzato. Questo permette di avere sempre il modificatore disponibile nel contesto degli ordini e di poterlo aggiornare di volta in volta.',
      'missing_movements_for_modifiers' => 'Alcuni tipi di movimento contabile non sono inclusi in questa lista in quanto non ne è stato definito il comportamento per tutti i metodi di pagamenti previsti in fase di consegna (:methods). Revisiona i tipi di movimento dal pannello Contabilità -> Tipi Movimenti',
      'type_for_modifier' => 'Selezionando un tipo di movimento contabile, al pagamento della consegna verrà generato un movimento con lo stesso valore del modificatore calcolato. Altrimenti, il valore del modificatore sarà incorporato nel pagamento della prenotazione stessa e andrà ad alterare il saldo complessivo del fornitore. Usa questa funzione se vuoi tenere traccia dettagliata degli importi pagati tramite questo modificatore.',
      'missing_method_for_movements_in_modifiers' => 'Attenzione! Ci sono tipi di movimento contabile associati a modificatori per i quali non è stato definito un comportamento per tutti i metodi di pagamento abilitati per le consegne. Si raccomanda di revisionarli, o potrebbero non essere correttamente applicati ai rispettivi modificatori (con potenziale perdita di informazioni in contabilità).',
      'missing_method_for_movement_in_modifier' => 'Attenzione! Questo tipo di movimento contabile è associato ad almeno un modificatore, ma non ha un comportamento definito per tutti i metodi di pagamento abilitati per il tipo movimento ":name". Si raccomanda di revisionarlo, o non sarà correttamente applicato al modificatore (con potenziale perdita di informazioni in contabilità).',
    ),
    'balances_history' => 'Storico Saldi',
    'current_balance' => 'Saldo Corrente',
    'registrar' => 'Registrato Da',
    'accepts_negative_value' => 'Accetta Valori Negativi',
    'fixed_value' => 'Valore Fisso',
    'debit' => 'Debito',
    'type' => 'Tipo Movimento Contabile',
    'credits_status' => 'Stato Crediti',
    'vat_rates' => 'Aliquote IVA',
    'recalculate_balances' => 'Ricalcola Saldi',
    'balances_archive' => 'Archivia Saldi',
    'all' => 'Movimenti',
    'name' => 'Movimento',
    'amount' => 'Importo',
    'types' => 'Tipi Movimenti',
    'invoices' => 'Fatture',
    'reference_for_modifier' => 'Riferimento su cui applicare il modificatore',
    'distribute_on' => 'Distribuzione sulle prenotazioni in base a',
    'to_pay' => 'Da Pagare',
    'available_credit' => 'Credito Disponibile',
    'always_active_modifiers' => 'Modificatore sempre attivo',
    'apply_theshold_to' => 'Misura su cui applicare le soglie',
    'sepa' =>
    array (
      'creditor_identifier' => 'Identificativo Creditore',
      'business_code' => 'Codice Univoco Azienda',
    ),
  ),
  'notifications' =>
  array (
    'global_filter' =>
    array (
      'roles' => 'Tutti gli utenti con ruolo :role',
      'orders' => 'Tutti i Partecipanti all\'ordine :supplier :number',
    ),
    'help' =>
    array (
      'repeat_mail_warning' => 'Questa notifica è già stata inoltrata via mail. Salvandola mantenendo questo flag attivo verrà inviata una nuova mail.',
      'sending_mail_warning' => 'Se abiliti questa opzione la notifica sarà subito inoltrata via mail. Se intendi modificarla prima di inoltrarla, attiva questa opzione solo dopo aver salvato e modificato la notifica.',
      'visibility_by_selection' => 'Se nessun utente viene selezionato, l\'elemento sarà visibile a tutti.',
      'suspend' => 'Se un ordine automatico viene sospeso, le prossime aperture verranno ignorate. Usa questa opzione per gestire i periodi di inattività del GAS, ad esempio durante le festività.',
      'arbitrary_dates' => 'Da qui è possibile aggiungere date arbitrarie al calendario delle consegne, anche per ordini non ancora esistenti. Questa funzione è consigliata per facilitare il coordinamento di diversi referenti all\'interno del GAS e pianificare le attività a lungo termine.',
      'types' => 'Le notifiche normali hanno una data di inizio e di fine, entro le quali essa viene visualizzata sulla dashboard degli utenti destinatari (i quali le possono comunque anche chiudere autonomamente).<br>Le notifiche permanenti viceversa rimangono sulla dashboard degli utenti destinatari a tempo indefinito, e possono essere rimosse solo dagli amministratori.<br>Le date sul calendario appaiono nel calendario della dashboard, e servono come promemoria di eventi che non necessariamente coinvolgono ordini e prenotazioni (e.g. assemblee, riunioni, incontri, altri appuntamenti...).',
    ),
    'cycle' =>
    array (
      'two_weeks' => 'Ogni due Settimane',
      'first_of_month' => 'Primo del Mese',
      'second_of_month' => 'Secondo del Mese',
      'third_of_month' => 'Terzo del Mese',
      'fourth_of_month' => 'Quarto del Mese',
      'last_of_month' => 'Ultimo del Mese',
    ),
    'name' => 'Notifica',
    'permanent_notification' => 'Notifica Permanente',
    'permanent' => 'Permanente',
    'notices' =>
    array (
      'new_notification_from' => 'Nuova notifica da parte di :author',
      'attached_order' => 'In allegato il file per l\'ordine di :gasname.',
    ),
    'recurrence' => 'Ricorrenza',
    'greetings' => 'Cordiali saluti',
    'send_to_current_users' => 'Notifica Utenti Visualizzati',
    'next_dates' => 'Prossime date in calendario:',
    'next_auto_orders' => 'Prossime aperture ordini automatici:',
    'list' =>
    array (
      'closed_orders' => 'Ordini Chiusi',
      'confirmed_dates' => 'Date Confermate',
      'temporary_dates' => 'Date Temporanee',
      'appointments' => 'Appuntamenti',
    ),
    'calendar_date' => 'Data sul Calendario',
    'date_reference' => 'Riferimento data',
  ),
  'orders' =>
  array (
    'booking' =>
    array (
      'void' => 'Annulla Prenotazione',
      'statuses' =>
      array (
        'open' => 'Prenotazioni Aperte',
        'closed' => 'Prenotazioni Chiuse',
        'shipped' => 'Consegnato',
        'paying' => 'Pagamento Utenti',
        'archived' => 'Archiviato',
        'suspended' => 'In Sospeso',
        'booked' => 'Prenotato',
        'to_deliver' => 'Da consegnare',
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
      'no_partecipating' => 'Non hai partecipato a quest\'ordine',
      'formatted_booked_amount' => 'Hai ordinato :amount',
      'formatted_booked_amount_with_friends' => 'Hai ordinato :amount + :friends',
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
      'unremovable_notice' => 'Questo meccanismo è deliberatamente non automatico e volutamente complesso, per evitare la perdita involontaria di dati.',
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
    'and_more' => 'e altri',
    'boxes' => 'Numero Confezioni',
    'supplier' => 'Fornitore',
    'booking_date_time' => 'Data/Ora Prenotazione',
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
      'with_modifiers' => 'Totale con Modificatori',
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
    'constraints' =>
    array (
      'quantity' => 'La quantità massima è 9999.99',
      'discrete' => 'La quantità di questo prodotto deve essere intera',
      'global_min' => 'Minimo Complessivo: :still (:global totale)',
      'global_max' => 'Disponibile: :still (:global totale)',
      'global_max_generic' => 'Quantità superiore alla disponibilità',
      'relative_max_formatted' => 'Massimo Consigliato: :quantity',
      'relative_max' => 'Quantità superiore al massimo consigliato',
      'completing_max_short' => ':icon Disponibile: :quantity',
      'completing_max_formatted' => 'Mancano :still :measure per completare la confezione per questo ordine',
      'completing_max' => 'Quantità superiore al massimo ordinabile per completare la confezione',
      'relative_min_formatted' => 'Minimo: :quantity',
      'relative_min' => 'Quantità inferiore al minimo consentito',
      'relative_multiple_formatted' => 'Multiplo: :quantity',
      'relative_multiple' => 'Quantità non multipla del valore consentito',
    ),
    'documents' =>
    array (
      'shipping' =>
      array (
        'filename' => 'Dettaglio Consegne ordini :suppliers.pdf',
        'heading' => 'Dettaglio Consegne Ordine :identifier a :supplier del :date',
        'short_heading' => 'Dettaglio Consegne del :date',
      ),
      'summary' =>
      array (
        'heading' => 'Prodotti ordine :identifier presso :supplier',
      ),
      'table' =>
      array (
        'filename' => 'Tabella Ordine :identifier presso :supplier.csv',
      ),
    ),
    'all' => 'Ordini',
    'pending_packages' => 'Confezioni Da Completare',
    'booking_aggregation' => 'Aggregazione Prenotazione',
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
  ),
  'permissions' =>
  array (
    'permissions' =>
    array (
      'maintenance_access' => 'Accesso consentito anche in manutenzione',
      'alter_permissions' => 'Modificare tutti i permessi',
      'alter_configs' => 'Modificare le configurazioni del GAS',
      'create_suppliers' => 'Creare nuovi fornitori',
      'do_booking' => 'Effettuare ordini',
      'view_suppliers' => 'Vedere tutti i fornitori',
      'view_orders' => 'Vedere tutti gli ordini',
      'alter_self' => 'Modificare la propria anagrafica',
      'delete_account' => 'Eliminare il proprio account',
      'admin_users' => 'Amministrare gli utenti',
      'view_users' => 'Vedere tutti gli utenti',
      'sub_users' => 'Avere sotto-utenti con funzioni limitate',
      'admin_user_movements' => 'Amministrare i movimenti contabili degli utenti',
      'admin_movements' => 'Amministrare tutti i movimenti contabili',
      'view_movements' => 'Vedere i movimenti contabili',
      'admin_movements_types' => 'Amministrare i tipi dei movimenti contabili',
      'admin_categories' => 'Amministrare le categorie',
      'admin_measures' => 'Amministrare le unità di misura',
      'view_statistics' => 'Visualizzare le statistiche',
      'admin_notifications' => 'Amministrare le notifiche',
      'alter_suppliers' => 'Modificare i fornitori assegnati',
      'open_orders' => 'Aprire e modificare ordini',
      'do_deliveries' => 'Effettuare le consegne',
      'admin_invoices' => 'Amministrare le fatture',
      'admin_supplier_movements' => 'Amministrare i movimenti contabili del fornitore',
      'admin_multigas' => 'Amministrare la modalità Multi-GAS su questa istanza',
    ),
    'roles' =>
    array (
      'admin' => 'Amministratore',
      'secondary_admin' => 'Amministratore GAS Secondario',
    ),
    'name' => 'Permessi',
    'supplier' =>
    array (
      'change' => 'Puoi modificare il fornitore',
      'orders' => 'Puoi aprire nuovi ordini per il fornitore',
      'deliveries' => 'Gestisci le consegne per il fornitore',
    ),
    'role' => 'Ruolo',
    'help' =>
    array (
      'global_permission_notice' => 'Questo permesso speciale si applica automaticamente a tutti i soggetti (presenti e futuri) e permette di agire su tutti, benché l\'utente assegnatario non sarà esplicitamente visibile dagli altri.',
      'blocked_autoremove' => 'Non puoi auto-revocarti questo ruolo amministrativo',
      'unique_role_warning' => 'Questo è l\'unico ruolo abilitato a questo permesso speciale: se lo revochi rischi di perdere il controllo dell\'istanza.',
      'unprivileged' => 'Questo ruolo sarà automaticamete assegnato ad ogni nuovo utente',
      'sub_user' => 'Questo ruolo sarà automaticamente assegnato ad ogni amico degli utenti esistenti. Si consiglia di creare un ruolo dedicato, con permessi limitati alle sole prenotazioni',
      'multigas_admin' => 'Questo ruolo sarà automaticamente assegnato al primo utente di ogni nuovo GAS creato nel pannello Multi-GAS',
      'admin_not_authorized' => 'Non sei autorizzato a gestire nessun ruolo.',
      'parent_role' => 'Gli utenti con assegnato il ruolo superiore potranno assegnare ad altri utenti questo ruolo',
      'missing_elements_warning' => 'A questo ruolo manca l\'assegnazione a uno o più elementi per i quali sono concessi permessi, ed il comportamento potrebbe non essere quello desiderato',
    ),
    'revoke' => 'Revoca Ruolo',
    'change_roles' => 'Edita Ruoli',
    'parent_role' => 'Ruolo Superiore',
    'add_user' => 'Cerca e Aggiungi Nuovo Utente',
    'unprivileged' => 'Ruolo Utente non Privilegiato',
    'sub_user' => 'Ruolo Sotto-Utente',
    'multigas_admin' => 'Ruolo Amministratore GAS Secondario',
  ),
  'products' =>
  array (
    'prices' =>
    array (
      'unit' => 'Prezzo Unitario',
      'unit_no_vat' => 'Prezzo Unitario (senza IVA)',
      'package' => 'Prezzo Confezione',
    ),
    'name' => 'Prodotto',
    'code' => 'Codice Fornitore',
    'bookable' => 'Ordinabile',
    'vat_rate' => 'Aliquota IVA',
    'portion_quantity' => 'Pezzatura',
    'multiple' => 'Multiplo',
    'min_quantity' => 'Minimo',
    'max_quantity' => 'Massimo Consigliato',
    'available' => 'Disponibile',
    'help' =>
    array (
      'unit_no_vat' => 'Da usare in combinazione con Aliquota IVA',
      'package_price' => 'Se specificato, il prezzo unitario viene calcolato come Prezzo Confezione / Dimensione Confezione',
      'importing_categories_and_measures' => 'Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.',
      'imported_notice' => 'Prodotti importati',
      'available_explain' => 'Quantità massima di prodotto che complessivamente può essere prenotata in un ordine',
      'bookable' => 'Indica se il prodotto potrà essere ordinato o meno all\'interno dei nuovi ordini per il fornitore',
      'pending_orders_change_price' => 'Ci sono ordini non ancora consegnati e archiviati in cui appare il prodotto di cui ha appena modificato il prezzo. Seleziona quelli in cui vuoi che venga applicato il nuovo prezzo (del prodotto e/o le differenze prezzi delle eventuali varianti).',
      'pending_orders_change_price_second' => 'Se modifichi i prezzi e nell\'ordine ci sono prenotazioni che sono già state consegnate, dovrai manualmente salvare nuovamente tali consegne affinché vengano rigenerati i nuovi movimenti contabili aggiornati.',
      'discrete_measure_selected_notice' => 'Hai selezionato una unità di misura discreta: per questo prodotto possono essere usate solo quantità intere.',
      'measure' => 'Unità di misura assegnata al prodotto. Attenzione: può influenzare l\'abilitazione di alcune variabili del prodotto, si veda il parametro Unità Discreta nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati)',
      'portion_quantity' => 'Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:<ul><li>unità di misura: chili</li><li>pezzatura: 0.3</li><li>prezzo unitario: 10 euro</li><li>quantità prenotata: 1 (che dunque si intende 1 pezzo da 0.3 chili)</li><li>costo: 1 x 0.3 x 10 = 3 euro</li></ul>Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva',
      'package_size' => 'Se il prodotto viene distribuito in confezioni da N pezzi, indicare qui il valore di N. Gli ordini da sottoporre al fornitore dovranno essere espressi in numero di confezioni, ovvero numero di pezzi ordinati / numero di pezzi nella confezione. Se la quantità totale di pezzi ordinati non è un multiplo di questo numero il prodotto sarà marcato con una icona rossa nel pannello di riassunto dell\'ordine, da cui sarà possibile sistemare le quantità aggiungendo e togliendo ove opportuno.',
      'multiple' => 'Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente. Da non confondere con l\'attributo Confezione',
      'min_quantity' => 'Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata',
      'max_quantity' => 'Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning',
      'available' => 'Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto',
      'global_min' => 'Se diverso da 0, questa è la quantità minima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto',
      'variants' => 'Ogni prodotto può avere delle varianti, ad esempio la taglia o il colore per i capi di abbigliamento. In fase di prenotazione, gli utenti potranno indicare quantità diverse per ogni combinazione di varianti.',
      'duplicate_notice' => 'Il duplicato avrà una copia delle varianti e dei modificatori del prodotto originario. Potranno essere eventualmente modificati dopo il salvataggio.',
      'unit_price' => 'Prezzo unitario per unità di misura. Si intende IVA inclusa, per maggiori dettagli si veda il campo Aliquota IVA. Può assumere un significato particolare quando viene attivata la Pezzatura',
      'vat_rate' => 'Le aliquote esistenti possono essere configurate nel pannello Configurazioni',
      'notice_removing_product_in_orders' => 'Il prodotto è attualmente incluso in ordini non ancora consegnati. Cosa vuoi fare?',
    ),
    'weight_with_measure' => 'Peso (in KG)',
    'list' => 'Prodotti',
    'sorting' => 'Ordinamento',
    'variant' =>
    array (
      'matrix' => 'Modifica Matrice Varianti',
      'help' =>
      array (
        'code' => 'Se non viene specificato, tutte le varianti usano il Codice Fornitore del prodotto principale.',
        'price_difference' => 'Differenza di prezzo, positiva o negativa, da applicare al prezzo del prodotto quando una specifica combinazione di varianti viene selezionata.',
      ),
      'price_difference' => 'Differenza Prezzo',
      'weight_difference' => 'Differenza Peso',
    ),
    'package_size' => 'Dimensione Confezione',
    'global_min' => 'Minimo Complessivo',
    'variants' => 'Varianti',
    'remove_confirm' => 'Vuoi davvero eliminare il prodotto :name?',
    'removing' =>
    array (
      'keep' => 'Lascia il prodotto',
      'leave' => 'Togli il prodotto ed elimina tutte le relative prenotazioni',
    ),
  ),
  'supplier' =>
  array (
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
  ),
  'tour' =>
  array (
    'welcome' =>
    array (
      'title' => 'Benvenuto in GASdotto!',
      'body' => 'Qui ti diamo qualche suggerimento per iniziare ad utilizzare questa nuova piattaforma...',
    ),
    'profile' =>
    array (
      'title' => 'I tuoi dati',
      'body' => 'Cliccando qui accedi al pannello dei tuoi dati personali, da cui poi cambiare il tuo indirizzo email, la tua password di accesso e molto altro.',
    ),
    'users' =>
    array (
      'title' => 'Gli altri utenti',
      'body' => 'Da qui consulti l\'elenco degli utenti, ne modifichi i parametri, e ne puoi invitare di nuovi (o li puoi importare da un file CSV).',
    ),
    'suppliers' =>
    array (
      'title' => 'I fornitori e i listini',
      'body' => 'Cliccando qui puoi consultare l\'elenco dei fornitori, crearne di nuovi, modificarli, e per ciascuno caricare o modificare il relativo listino.',
    ),
    'orders' =>
    array (
      'title' => 'Gli ordini',
      'body' => 'Da questa pagina accedi all\'elenco degli ordini, da cui crearli e modificarli. Cliccando su ciascun ordine puoi trovare anche la tab \'Consegne\' per tenere traccia delle consegne e generare i movimenti contabili di pagamento.',
    ),
    'bookings' =>
    array (
      'title' => 'Le prenotazioni',
      'body' => 'Qui trovi l\'elenco degli ordini attualmente in corso, e puoi sottoporre le tue prenotazioni: clicca su ciascun ordine, e specifica la quantità desiderata per ogni prodotto.',
    ),
    'accounting' =>
    array (
      'title' => 'La contabilità',
      'body' => 'In questa pagina trovi tutti i movimenti contabili ed i relativi strumenti di amministrazione.',
    ),
    'config' =>
    array (
      'title' => 'Tutte le configurazioni',
      'body' => 'Cliccando qui trovi una moltitudine di parametri per personalizare il comportamento di questa istanza GASdotto.',
    ),
    'inline' =>
    array (
      'title' => 'Help in linea',
      'body' => 'Aprendo i diversi pannelli di GASdotto, accanto a molti parametri trovi una icona blu: passandoci sopra il cursore del mouse, o pigiandoci sopra con il dito usando lo smartphone, ti viene mostrato un breve testo descrittivo che te ne illustra i dettagli.',
    ),
    'last' =>
    array (
      'title' => 'Dubbi?',
      'body' => 'Se hai un dubbio sull\'utilizzo di GASdotto, o una segnalazione, o una richiesta, cliccando qui trovi i nostri contatti.',
    ),
    'finished' => 'Finito',
  ),
  'user' =>
  array (
    'help' =>
    array (
      'suspended' => 'Gli utenti Sospesi e Cessati non possono accedere alla piattaforma, pur restando registrati. È necessario specificare una data di cessazione/sospensione.',
      'wrong_control_error' => 'Il codice di controllo è sbagliato',
      'existing_email_error' => 'L\'indirizzo e-mail è già registrato.',
      'duplicated_name_error' => 'Questi nome e cognome sono già presenti sul DB. Si raccomanda di contattare l\'amministratore dell\'istanza per maggiori informazioni.',
      'waiting_approval' => 'Questo utente è in attesa di approvazione!',
      'promote_friend' => 'Cliccando "Salva", questo utente diventerà un utente regolare. Gli sarà assegnato il ruolo :role, avrà una propria contabilità, e non potrà più essere amministrato da :ex_parent. Sarà preservato lo storico delle sue prenotazioni, ma tutti i suoi pagamenti pregressi resteranno addebitati a :ex_parent.',
      'promote_friend_enforce_mail' => 'È necessario specificare almeno un indirizzo email di contatto del nuovo utente',
      'reassign_friend' => 'Da qui è possibile riassegnare un amico ad un altro utente. Tutti i pagamenti pregressi resteranno addebitati a :ex_parent.',
      'notifications_instructions' => 'Seleziona i fornitori per i quali vuoi ricevere una notifica all\'apertura di nuovi ordini.',
      'fee' => 'Dati relativi alla quota associativa dell\'utente, che scade ogni anno. Per disabilitare questa opzione, vai in Configurazione -> Contabilità',
      'deposit' => 'Dati relativi al deposito pagato dall\'utente al momento dell\'iscrizione al GAS. Per disabilitare questa opzione, vai in Configurazione -> Contabilità',
      'satispay' => 'Da qui puoi ricaricare il tuo credito utilizzando Satispay. Specifica quanto vuoi versare ed eventuali note per gli amministratori; riceverai una notifica sul tuo smartphone per confermare, entro 15 minuti, il versamento.',
      'remove_profile' => 'Vuoi davvero eliminare questo account? Tutti i dati personali saranno anonimizzati, benché sarà preservato lo storico delle prenotazioni.',
      'remove_profile_credit_notice' => 'Prima di procedere, è consigliato contattare i referenti del GAS per regolare i conti sul credito.',
      'importing' =>
      array (
        'user' =>
        array (
          'balance' => 'Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!',
        ),
      ),
    ),
    'firstname' => 'Nome',
    'lastname' => 'Cognome',
    'change_friend' => 'Modifica Amico',
    'formatted_aggregation' => 'Aggregazione :name',
    'sepa' =>
    array (
      'mandate' => 'Mandato SEPA',
      'date' => 'Data Mandato SEPA',
      'intro' => 'Configurazione SEPA',
      'help' => 'Specifica qui i parametri per la generazione dei RID per questo utente. Per gli utenti per i quali questi campi non sono stati compilati non sarà possibile generare alcun RID.',
      'identifier' => 'Identificativo Mandato SEPA',
    ),
    'last_login' => 'Ultimo Accesso',
    'last_booking' => 'Ultima Prenotazione',
    'member_since' => 'Membro da',
    'birthplace' => 'Luogo di Nascita',
    'birthdate' => 'Data di Nascita',
    'other_bookings' => 'Altre Prenotazioni',
    'fullname' => 'Nome Completo',
    'taxcode' => 'Codice Fiscale',
    'card_number' => 'Numero Tessera',
    'payment_method' => 'Modalità Pagamento',
    'all' => 'Utenti',
    'payed_fee' => 'Quota Pagata',
    'name' => 'Utente',
    'address_part' =>
    array (
      'street' => 'Indirizzo (Via)',
      'city' => 'Indirizzo (Città)',
      'zip' => 'Indirizzo (CAP)',
    ),
    'statuses' =>
    array (
      'active' => 'Attivo',
      'suspended' => 'Sospeso',
      'deleted' => 'Cessato',
      'removed' => 'Rimosso',
    ),
    'friend' => 'Amico',
    'removed_user' => 'Utente Rimosso',
    'booking_friend_header' => 'Ha ordinato :amount',
    'pending_deliveries' => 'Questa persona oggi deve ritirare anche altre prenotazioni:',
    'without_aggregation' => 'Senza Aggregazioni',
    'aggregation' => 'Aggregazione Utente',
    'credit_below_zero' => 'Credito < 0',
    'fee_not_payed' => 'Quota non Pagata',
    'personal_data' => 'Anagrafica',
    'approve' => 'Approva',
    'do_not_approve' => 'Non Approvare ed Elimina',
    'family_members' => 'Persone in Famiglia',
    'promote_friend' => 'Promuovi a utente regolare',
    'reassign_friend' => 'Cambia assegnazione',
    'change_friend_assignee' => 'Nuovo assegnatario',
    'fee' => 'Quota Associativa',
    'deposit' => 'Deposito',
    'fees_status' => 'Stato Quote',
    'all_ceased' => 'Cessati',
    'notices' =>
    array (
      'new_user' => 'Nuovo utente registrato su :gasname:',
      'pending_approval' => 'Il nuovo utente è in attesa di revisione: consulta il pannello di amministrazione per approvarlo o eliminarlo.',
    ),
    'last_fee' => 'Ultima Quota Versata',
    'fees' =>
    array (
      'new' => 'Nuova Quota',
      'change' => 'Modifica Quota',
    ),
    'empty' =>
    array (
      'friends' => 'Aggiungi le informazioni relative agli amici per i quali vuoi creare delle sotto-prenotazioni. Ogni singola prenotazione sarà autonoma, ma trattata come una sola in fase di consegna. Ogni amico può anche avere delle proprie credenziali di accesso, per entrare in GASdotto e popolare da sé le proprie prenotazioni.',
    ),
    'satispay' =>
    array (
      'reload' => 'Ricarica Credito con Satispay',
    ),
    'remove_profile' => 'Elimina profilo',
    'assign_aggregations' => 'Assegna Aggregazioni',
  ),
);
