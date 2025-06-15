<?php

return array (
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
  'mail' => 
  array (
    'welcome' => 
    array (
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
  ),
  'newuser' => 
  array (
    'description_manual' => 'Messaggio inviato ai nuovi iscritti registrati sulla piattaforma, in attesa di approvazione.',
    'description' => 'Messaggio inviato ai nuovi iscritti registrati sulla piattaforma.',
  ),
);