<?php

return array (
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
);
