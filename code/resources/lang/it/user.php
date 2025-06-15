<?php

return array (
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
);