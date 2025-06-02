<?php

return array (
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
);