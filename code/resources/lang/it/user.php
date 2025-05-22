<?php

return array (
  'firstname' => 'Nome',
  'lastname' => 'Cognome',
  'change_friend' => 'Cambia assegnazione',
  'sepa' => 
  array (
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
  'taxcode' => 'Codice Fiscale',
  'card_number' => 'Numero Tessera',
  'payment_method' => 'Modalità Pagamento',
  'all' => 'Utenti',
  'name' => 'Utente',
  'statuses' => 
  array (
    'active' => 'Attivo',
    'suspended' => 'Sospeso',
    'deleted' => 'Cessato',
  ),
  'friend' => 'Amico',
  'personal_data' => 'Anagrafica',
  'help' => 
  array (
    'waiting_approval' => 'Questo utente è in attesa di approvazione!',
    'promote_friend' => 'Cliccando "Salva", questo utente diventerà un utente regolare. Gli sarà assegnato il ruolo :role, avrà una propria contabilità, e non potrà più essere amministrato da :ex_parent. Sarà preservato lo storico delle sue prenotazioni, ma tutti i suoi pagamenti pregressi resteranno addebitati a :ex_parent.',
    'promote_friend_enforce_mail' => 'È necessario specificare almeno un indirizzo email di contatto del nuovo utente',
    'change_friend' => 'Da qui è possibile riassegnare un amico ad un altro utente. Tutti i pagamenti pregressi resteranno addebitati a :ex_parent.',
    'notifications_instructions' => 'Seleziona i fornitori per i quali vuoi ricevere una notifica all\'apertura di nuovi ordini.',
    'fee' => 'Dati relativi alla quota associativa dell\'utente, che scade ogni anno. Per disabilitare questa opzione, vai in Configurazione -> Contabilità',
    'deposit' => 'Dati relativi al deposito pagato dall\'utente al momento dell\'iscrizione al GAS. Per disabilitare questa opzione, vai in Configurazione -> Contabilità',
    'satispay' => 'Da qui puoi ricaricare il tuo credito utilizzando Satispay. Specifica quanto vuoi versare ed eventuali note per gli amministratori; riceverai una notifica sul tuo smartphone per confermare, entro 15 minuti, il versamento.',
    'remove_profile' => 'Vuoi davvero eliminare questo account? Tutti i dati personali saranno anonimizzati, benché sarà preservato lo storico delle prenotazioni.',
    'remove_profile_credit_notice' => 'Prima di procedere, è consigliato contattare i referenti del GAS per regolare i conti sul credito.',
  ),
  'approve' => 'Approva',
  'do_not_approve' => 'Non Approvare ed Elimina',
  'family_members' => 'Persone in Famiglia',
  'promote_friend' => 'Promuovi a utente regolare',
  'change_friend_assignee' => 'Nuovo assegnatario',
  'fee' => 'Quota Associativa',
  'deposit' => 'Deposito',
  'fees_status' => 'Stato Quote',
  'all_ceased' => 'Cessati',
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